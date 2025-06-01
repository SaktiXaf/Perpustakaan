<?php
require_once '../../config/koneksi.php';
checkLoginSession();

if (!isAdmin()) {
    header("Location: ../dashboard.php");
    exit();
}

$id = isset($_GET['id']) ? $_GET['id'] : null;
$confirm = isset($_GET['confirm']) ? $_GET['confirm'] : false;

if (!$id) {
    header("Location: list_user.php");
    exit();
}

// Prevent deletion of own account
if ($id == $_SESSION['user_id']) {
    $_SESSION['error'] = "Tidak dapat menghapus akun yang sedang login!";
    header("Location: list_user.php");
    exit();
}

try {
    if ($confirm === 'true') {
        // Get user info first for success message
        $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        // Delete the user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['success'] = "User \"" . htmlspecialchars($user['username']) . "\" berhasil dihapus!";
        header("Location: list_user.php");
        exit();
    }

    // Get user data for confirmation
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['error'] = "User tidak ditemukan!";
        header("Location: list_user.php");
        exit();
    }
} catch(PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: list_user.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus User - Sistem Perpustakaan</title>
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
        .user-info {
            background: rgba(220, 53, 69, 0.1);
            padding: 1rem;
            border-radius: 10px;
            margin: 1.5rem 0;
        }
        .username {
            font-weight: 600;
            color: #dc3545;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }
        .user-details {
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
            <i class="fas fa-user-times delete-icon"></i>
            <h2 class="delete-title">Konfirmasi Hapus</h2>
            
            <div class="user-info">
                <div class="username"><?php echo htmlspecialchars($user['username']); ?></div>
                <div class="user-details">
                    <p class="mb-1"><strong>Role:</strong> <?php echo ucfirst(htmlspecialchars($user['role'])); ?></p>
                    <p class="mb-0"><strong>ID:</strong> <?php echo htmlspecialchars($user['id']); ?></p>
                </div>
            </div>
            
            <p class="text-muted mb-4">
                Apakah Anda yakin ingin menghapus user ini?<br>
                <small class="text-danger">Tindakan ini tidak dapat dibatalkan.</small>
            </p>
            
            <div class="d-flex justify-content-center">
                <a href="hapus_user.php?id=<?php echo $id; ?>&confirm=true" class="btn btn-danger">
                    <i class="fas fa-trash me-2"></i>Ya, Hapus
                </a>
                <a href="list_user.php" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Batal
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
