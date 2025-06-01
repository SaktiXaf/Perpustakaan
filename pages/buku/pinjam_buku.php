<?php
require_once '../../config/koneksi.php';
checkLoginSession();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $buku_id = $_POST['buku_id'];
    $user_id = $_SESSION['user_id'];
    $tanggal_pinjam = date('Y-m-d');

    try {
        $conn->beginTransaction();

        // Check if book is available
        $stmt = $conn->prepare("SELECT stok FROM buku WHERE id = :buku_id FOR UPDATE");
        $stmt->bindParam(':buku_id', $buku_id);
        $stmt->execute();
        $book = $stmt->fetch();

        if (!$book || $book['stok'] <= 0) {
            throw new Exception('Buku tidak tersedia untuk dipinjam.');
        }

        // Create borrowing record
        $stmt = $conn->prepare("INSERT INTO peminjaman (user_id, buku_id, tanggal_pinjam, status) VALUES (:user_id, :buku_id, :tanggal_pinjam, 'dipinjam')");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':buku_id', $buku_id);
        $stmt->bindParam(':tanggal_pinjam', $tanggal_pinjam);
        $stmt->execute();

        // Update book stock
        $stmt = $conn->prepare("UPDATE buku SET stok = stok - 1 WHERE id = :buku_id");
        $stmt->bindParam(':buku_id', $buku_id);
        $stmt->execute();

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Buku berhasil dipinjam']);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
