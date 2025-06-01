<?php
require_once '../../config/koneksi.php';
checkLoginSession();

header('Content-Type: application/json');

/**
 * Helper function to send JSON response
 */
function sendJsonResponse($success, $message, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
    exit;
}

/**
 * Validate required POST parameters
 */
function validatePostData() {
    $requiredFields = ['buku_id', 'action', 'jumlah'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
            sendJsonResponse(false, "Field '$field' harus diisi.");
        }
    }

    // Validate buku_id
    $buku_id = filter_var($_POST['buku_id'], FILTER_VALIDATE_INT);
    if ($buku_id === false || $buku_id <= 0) {
        sendJsonResponse(false, 'ID buku tidak valid.');
    }

    // Validate jumlah
    $jumlah = filter_var($_POST['jumlah'], FILTER_VALIDATE_INT);
    if ($jumlah === false || $jumlah <= 0) {
        sendJsonResponse(false, 'Jumlah pinjam harus lebih dari 0.');
    }

    // Validate action
    $action = trim($_POST['action']);
    if (!in_array($action, ['pinjam', 'kembali'])) {
        sendJsonResponse(false, 'Action tidak valid.');
    }

    return [
        'buku_id' => $buku_id,
        'jumlah' => $jumlah,
        'action' => $action,
        'user_id' => $_SESSION['user_id']
    ];
}

// Start main process
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Method not allowed', 405);
}

// Validate input data
$data = validatePostData();    try {
        $conn->beginTransaction();

        // Check book availability with lock
        $stmt = $conn->prepare("
            SELECT id, judul, stok 
            FROM buku 
            WHERE id = :buku_id 
            FOR UPDATE
        ");
        $stmt->bindParam(':buku_id', $data['buku_id']);
        $stmt->execute();
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data['action'] === 'pinjam') {
            // Validate book exists
            if (!$book) {
                throw new Exception('Buku tidak ditemukan.');
            }
            
            // Validate sufficient stock
            if ($book['stok'] < $data['jumlah']) {
                throw new Exception('Stok buku tidak mencukupi. Stok tersedia: ' . $book['stok']);
            }

            // Check for existing active borrowing
            $stmt = $conn->prepare("
                SELECT COUNT(*) as count 
                FROM peminjaman 
                WHERE user_id = :user_id 
                AND buku_id = :buku_id 
                AND status = 'dipinjam'
            ");
            $stmt->bindParam(':user_id', $data['user_id']);
            $stmt->bindParam(':buku_id', $data['buku_id']);
            $stmt->execute();
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing['count'] > 0) {
                throw new Exception('Anda sudah meminjam buku ini dan belum mengembalikannya.');
            }
            
            // Create borrowing record
            $tanggal_pinjam = date('Y-m-d');
            $stmt = $conn->prepare("                INSERT INTO peminjaman (
                    user_id, 
                    buku_id, 
                    jumlah, 
                    tanggal_pinjam, 
                    status
                ) VALUES (
                    :user_id, 
                    :buku_id, 
                    :jumlah, 
                    :tanggal_pinjam, 
                    'dipinjam'
                )
            ");
            $stmt->bindParam(':user_id', $data['user_id']);
            $stmt->bindParam(':buku_id', $data['buku_id']);
            $stmt->bindParam(':jumlah', $data['jumlah']);
            $stmt->bindParam(':tanggal_pinjam', $tanggal_pinjam);
            
            if (!$stmt->execute()) {
                throw new Exception('Gagal membuat record peminjaman.');
            }            // Update book stock with validation
            $stmt = $conn->prepare("
                UPDATE buku 
                SET stok = stok - :jumlah 
                WHERE id = :buku_id 
                AND stok >= :jumlah
            ");
            $stmt->bindParam(':jumlah', $data['jumlah']);
            $stmt->bindParam(':buku_id', $data['buku_id']);
            $stmt->execute();

            // Verify stock update was successful
            if ($stmt->rowCount() === 0) {
                throw new Exception('Gagal mengupdate stok buku. Mohon coba lagi.');
            }

            $message = 'Buku berhasil dipinjam.';
        } 
        else if ($data['action'] === 'kembali') {
            if (!isset($_POST['pinjam_id'])) {
                throw new Exception('ID peminjaman diperlukan untuk pengembalian.');
            }

            $pinjam_id = filter_var($_POST['pinjam_id'], FILTER_VALIDATE_INT);
            if ($pinjam_id === false || $pinjam_id <= 0) {
                throw new Exception('ID peminjaman tidak valid.');
            }

            // Get and lock the borrowing record
            $stmt = $conn->prepare("
                SELECT * FROM peminjaman 
                WHERE id = :pinjam_id 
                AND user_id = :user_id 
                AND status = 'dipinjam' 
                FOR UPDATE
            ");
            $stmt->bindParam(':pinjam_id', $pinjam_id);
            $stmt->bindParam(':user_id', $data['user_id']);
            $stmt->execute();
            $pinjam = $stmt->fetch(PDO::FETCH_ASSOC);            if (!$pinjam) {
                throw new Exception('Data peminjaman tidak ditemukan atau sudah dikembalikan.');
            }

            // Update borrowing status
            $stmt = $conn->prepare("
                UPDATE peminjaman 
                SET status = 'dikembalikan', 
                    tanggal_kembali = CURRENT_TIMESTAMP 
                WHERE id = :pinjam_id 
                AND status = 'dipinjam'
            ");
            $stmt->bindParam(':pinjam_id', $pinjam_id);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                throw new Exception('Gagal mengupdate status peminjaman.');
            }

            // Return book stock
            $stmt = $conn->prepare("
                UPDATE buku 
                SET stok = stok + :jumlah 
                WHERE id = :buku_id
            ");
            $stmt->bindParam(':jumlah', $pinjam['jumlah']);
            $stmt->bindParam(':buku_id', $pinjam['buku_id']);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                throw new Exception('Gagal mengembalikan stok buku.');
            }

            $message = 'Buku berhasil dikembalikan.';
        }

        $conn->commit();
        sendJsonResponse(true, $message);
    } 
    catch (Exception $e) {
        $conn->rollBack();
        sendJsonResponse(false, $e->getMessage());
    }
