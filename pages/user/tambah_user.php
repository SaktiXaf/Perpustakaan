<?php
require_once '../../config/koneksi.php';
checkLoginSession();

if (!isAdmin()) {
    header("Location: ../dashboard.php");
    exit();
}

// Check if users table exists
try {
    $stmt = $conn->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() === 0) {
        // Create users table if it doesn't exist
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->exec($sql);
    }
} catch(PDOException $e) {
    die("Error checking/creating table: " . $e->getMessage());
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $role = $_POST['role'] ?? '';

        // Debug log
        error_log("Form submitted - Username: $username, Role: $role");

        // Validation
        if (empty($username) || empty($password) || empty($confirm_password) || empty($role)) {
            $error = "Semua field harus diisi!";
            error_log("Validation failed - Empty fields detected");
        } elseif ($password !== $confirm_password) {
            $error = "Konfirmasi password tidak cocok!";
            error_log("Validation failed - Password mismatch");
        } else {
            try {
                // Check if username already exists
                $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetchColumn() > 0) {
                    $error = "Username sudah digunakan!";
                    error_log("User creation failed - Username already exists: $username");
                } else {
                    // Insert new user
                    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt->execute([$username, $hashed_password, $role]);
                    
                    if ($stmt->rowCount() > 0) {
                        $success = "User berhasil ditambahkan!";
                        error_log("User created successfully - Username: $username, Role: $role");
                        
                        // Clear form
                        $username = '';
                        $role = '';
                    } else {
                        $error = "Gagal menambahkan user - Tidak ada baris yang terpengaruh";
                        error_log("User creation failed - No rows affected");
                    }
                }
            } catch(PDOException $e) {
                $error = "Error: " . $e->getMessage();
                error_log("Database error during user creation: " . $e->getMessage());
            }
        }
    } catch(Exception $e) {
        $error = "Terjadi kesalahan sistem";
        error_log("Unexpected error during form processing: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User - Sistem Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #1a2a6c, #b21f1f) !important;
            padding: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-weight: 600;
            font-size: 1.5rem;
        }
        .nav-link {
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            transform: translateY(-2px);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .card-body {
            padding: 2rem;
        }
        h2 {
            color: #1a2a6c;
            font-weight: 600;
            margin-bottom: 2rem;
        }
        .form-label {
            font-weight: 500;
            color: #1a2a6c;
            margin-bottom: 0.5rem;
        }
        .form-control {
            border-radius: 10px;
            padding: 0.8rem 1rem;
            border: 1px solid #e0e0e0;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(26, 42, 108, 0.1);
            border-color: #1a2a6c;
        }
        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: linear-gradient(45deg, #1a2a6c, #b21f1f);
            border: none;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 42, 108, 0.2);
            background: linear-gradient(45deg, #15215a, #981b1b);
        }
        .btn-secondary {
            background: #6c757d;
            border: none;
        }
        .btn-secondary:hover {
            transform: translateY(-2px);
            background: #5a6268;
        }
        .alert {
            border: none;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .alert-success {
            background: #e3fcef;
            color: #00875a;
        }
        .alert-danger {
            background: #ffe8e8;
            color: #dc3545;
        }
        .password-toggle {
            position: relative;
        }
        .password-toggle .toggle-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Perpustakaan</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../buku/list_buku.php">Daftar Buku</a>
                    </li>
                    <?php if (isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../buku/tambah_buku.php">Tambah Buku</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="list_user.php">Kelola User</a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="d-flex align-items-center mb-4" data-aos="fade-up">
                    <i class="fas fa-user-plus fa-2x me-3" style="color: #1a2a6c;"></i>
                    <h2 class="mb-0">Tambah User</h2>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger" data-aos="fade-up">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success" data-aos="fade-up">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <div class="card" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-body">
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user me-2"></i>Username
                                </label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    Username harus diisi
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Password
                                </label>
                                <div class="password-toggle">
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <i class="fas fa-eye-slash toggle-icon" onclick="togglePassword('password')"></i>
                                </div>
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    Password harus diisi
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Konfirmasi Password
                                </label>
                                <div class="password-toggle">
                                    <input type="password" class="form-control" id="confirm_password" 
                                           name="confirm_password" required>
                                    <i class="fas fa-eye-slash toggle-icon" onclick="togglePassword('confirm_password')"></i>
                                </div>
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    Konfirmasi password harus diisi
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="role" class="form-label">
                                    <i class="fas fa-user-shield me-2"></i>Role
                                </label>
                                <select class="form-control" id="role" name="role" required>
                                    <option value="">Pilih Role</option>
                                    <option value="admin" <?php echo (isset($role) && $role === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                    <option value="user" <?php echo (isset($role) && $role === 'user') ? 'selected' : ''; ?>>User</option>
                                </select>
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    Role harus dipilih
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan
                                </button>
                                <a href="list_user.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true
        });

        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.needs-validation');
            
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                
                const username = document.getElementById('username');
                const password = document.getElementById('password');
                const confirm_password = document.getElementById('confirm_password');
                const role = document.getElementById('role');
                let isValid = true;

                // Clear previous validation states
                form.classList.remove('was-validated');
                
                // Validate username
                if (!username.value.trim()) {
                    isValid = false;
                    showError('Username harus diisi');
                }

                // Validate password
                if (!password.value) {
                    isValid = false;
                    showError('Password harus diisi');
                }

                // Validate confirm password
                if (!confirm_password.value) {
                    isValid = false;
                    showError('Konfirmasi password harus diisi');
                } else if (password.value !== confirm_password.value) {
                    isValid = false;
                    showError('Password dan konfirmasi password tidak cocok');
                }

                // Validate role
                if (!role.value) {
                    isValid = false;
                    showError('Role harus dipilih');
                }

                if (isValid) {
                    console.log('Form is valid, submitting...');
                    form.submit();
                }
            });
            
            function showError(message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            }
        });

        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling;
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }

        // Add form input animations
        document.querySelectorAll('.form-control, .form-select').forEach(input => {
            input.addEventListener('focus', function() {
                this.style.transform = 'scale(1.02)';
                this.style.transition = 'all 0.3s ease';
            });
            input.addEventListener('blur', function() {
                this.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>
