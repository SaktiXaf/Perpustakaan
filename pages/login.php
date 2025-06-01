<?php
require_once '../config/koneksi.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Perpustakaan</title>    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>        
    
    body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            overflow-x: hidden;
        }
        .container-fluid {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            width: 100%;
            padding: 0;
            margin: 0;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            margin: auto;
        }
        .card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        .card-body {
            padding: 40px;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .app-title {
            font-size: 24px;
            font-weight: 600;
            color: #1a2a6c;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: 500;
            color: #333;
        }
        .input-group {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }
        .input-group-text {
            border: 1px solid #e0e0e0;
            padding: 12px;
        }
        .form-control {
            border: 1px solid #e0e0e0;
            padding: 12px;
            font-size: 14px;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #1a2a6c;
        }
        .btn-login {
            background: linear-gradient(45deg, #1a2a6c, #b21f1f);
            border: none;
            color: white;
            padding: 12px;
            border-radius: 10px;
            font-weight: 500;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(26, 42, 108, 0.2);
            background: linear-gradient(45deg, #15215a, #981b1b);
        }
        .alert {
            border-radius: 10px;
            font-size: 14px;
            padding: 15px;
            margin-bottom: 25px;
        }
        @media (max-width: 576px) {
            .login-container {
                padding: 10px;
            }
            .card-body {
                padding: 20px;
            }
            .app-title {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>    <div class="container-fluid">
        <div class="login-container">
            <div class="card">
                <div class="card-body">                    <div class="logo-container">
                        <i class="fas fa-book-reader fa-3x mb-3" style="background: linear-gradient(45deg, #1a2a6c, #b21f1f); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                    </div>
                            <h1 class="app-title text-center">Perpustakaan Digital</h1>
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            <form method="POST">
                                <div class="form-group">
                                    <label for="username" class="form-label">Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent border-end-0">
                                            <i class="fas fa-user text-muted"></i>
                                        </span>
                                        <input type="text" class="form-control border-start-0 ps-0" 
                                               id="username" name="username" required 
                                               placeholder="Masukkan username">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent border-end-0">
                                            <i class="fas fa-lock text-muted"></i>
                                        </span>
                                        <input type="password" class="form-control border-start-0 ps-0" 
                                               id="password" name="password" required 
                                               placeholder="Masukkan password">
                                    </div>
                                </div>
                                <button type="submit" name="login" class="btn btn-login w-100">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                            </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
