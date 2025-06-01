<?php
require_once '../../config/koneksi.php';
checkLoginSession();

if (!isAdmin()) {
    header("Location: list_buku.php");
    exit();
}

$id = isset($_GET['id']) ? $_GET['id'] : null;
$confirm = isset($_GET['confirm']) ? $_GET['confirm'] : false;

if (!$id) {
    header("Location: list_buku.php");
    exit();
}

try {
    if ($confirm === 'true') {
        // Get book info first for success message
        $stmt = $conn->prepare("SELECT judul, cover_image FROM buku WHERE id = ?");
        $stmt->execute([$id]);
        $book = $stmt->fetch();
        
        // Delete the book
        $stmt = $conn->prepare("DELETE FROM buku WHERE id = ?");
        $stmt->execute([$id]);

        // Delete the cover image if exists
        if ($book['cover_image'] && file_exists('../../' . $book['cover_image'])) {
            unlink('../../' . $book['cover_image']);
        }
        
        $_SESSION['success'] = "Buku \"" . htmlspecialchars($book['judul']) . "\" berhasil dihapus!";
        header("Location: list_buku.php");
        exit();
    }

    // Get book data for confirmation
    $stmt = $conn->prepare("SELECT * FROM buku WHERE id = ?");
    $stmt->execute([$id]);
    $book = $stmt->fetch();
    
    if (!$book) {
        $_SESSION['error'] = "Buku tidak ditemukan!";
        header("Location: list_buku.php");
        exit();
    }
} catch(PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: list_buku.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Buku - Sistem Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .delete-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 500px;
            padding: 2rem;
            text-align: center;
            animation: slideIn 0.5s ease-out;
        }
        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        .delete-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 1.5rem;
            animation: shake 0.5s ease-in-out;
        }
        @keyframes shake {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-10deg); }
            75% { transform: rotate(10deg); }
        }
        .delete-title {
            color: #1a2a6c;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .book-info {
            background: rgba(220, 53, 69, 0.1);
            padding: 1rem;
            border-radius: 10px;
            margin: 1.5rem 0;
        }
        .book-title {
            font-weight: 600;
            color: #dc3545;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }
        .book-details {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 0 0.5rem;
        }
        .btn-danger {
            background: linear-gradient(45deg, #dc3545, #fd1d1d);
            border: none;
        }
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.2);
            background: linear-gradient(45deg, #c82333, #e31c3d);
        }
        .btn-secondary {
            background: #6c757d;
            border: none;
        }
        .btn-secondary:hover {
            transform: translateY(-2px);
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="delete-card">
            <i class="fas fa-trash-alt delete-icon"></i>
            <h2 class="delete-title">Konfirmasi Hapus</h2>
            
            <div class="book-info">
                <div class="book-title"><?php echo htmlspecialchars($book['judul']); ?></div>
                <div class="book-details">
                    <p class="mb-1"><strong>Pengarang:</strong> <?php echo htmlspecialchars($book['pengarang']); ?></p>
                    <p class="mb-1"><strong>Tahun Terbit:</strong> <?php echo htmlspecialchars($book['tahun_terbit']); ?></p>
                    <p class="mb-0"><strong>Stok:</strong> <?php echo htmlspecialchars($book['stok']); ?> buku</p>
                </div>
            </div>
            
            <p class="text-muted mb-4">
                Apakah Anda yakin ingin menghapus buku ini?<br>
                <small class="text-danger">Tindakan ini tidak dapat dibatalkan.</small>
            </p>
            
            <div class="d-flex justify-content-center">
                <a href="hapus_buku.php?id=<?php echo $id; ?>&confirm=true" class="btn btn-danger">
                    <i class="fas fa-trash me-2"></i>Ya, Hapus
                </a>
                <a href="list_buku.php" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Batal
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
