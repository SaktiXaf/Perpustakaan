<?php
require_once '../../config/koneksi.php';
checkLoginSession();

if (!isAdmin()) {
    header("Location: list_buku.php");
    exit();
}

$id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$id) {
    header("Location: list_buku.php");
    exit();
}

$error = '';
$success = '';

// Get book data
try {    // Try to add cover_image column if it doesn't exist
    $stmt = $conn->prepare("SHOW COLUMNS FROM buku LIKE 'cover_image'");
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        $stmt = $conn->prepare("ALTER TABLE buku ADD COLUMN cover_image VARCHAR(255)");
        $stmt->execute();
    }

    $stmt = $conn->prepare("SELECT * FROM buku WHERE id = ?");
    $stmt->execute([$id]);
    $book = $stmt->fetch();
    if (!$book) {
        header("Location: list_buku.php");
        exit();
    }
    
    // Initialize cover_image if it doesn't exist
    if (!isset($book['cover_image'])) {
        $book['cover_image'] = null;
    }
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $pengarang = $_POST['pengarang'];
    $tahun_terbit = $_POST['tahun_terbit'];
    $isbn = $_POST['isbn'];
    $stok = $_POST['stok'];
    $cover_image = $book['cover_image']; // Keep existing image by default

    // Handle image upload
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['cover_image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            $error = "Format file harus JPG atau PNG!";
        } else {
            // Generate unique filename
            $fileName = uniqid() . '_' . basename($file['name']);
            $uploadPath = '../../assets/book_covers/' . $fileName;
            
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Delete old image if exists
                if ($book['cover_image'] && file_exists('../../' . $book['cover_image'])) {
                    unlink('../../' . $book['cover_image']);
                }
                $cover_image = 'assets/book_covers/' . $fileName;
            } else {
                $error = "Gagal mengupload gambar!";
            }
        }
    }

    // Validation
    if (empty($judul) || empty($pengarang) || empty($tahun_terbit) || empty($isbn) || empty($stok)) {
        $error = "Semua field harus diisi!";
    } elseif (!preg_match("/^\d{4}$/", $tahun_terbit)) {
        $error = "Format tahun terbit harus 4 digit!";
    } else {
        try {
            $stmt = $conn->prepare("UPDATE buku SET judul = ?, pengarang = ?, tahun_terbit = ?, isbn = ?, stok = ?, cover_image = ? WHERE id = ?");
            $stmt->execute([$judul, $pengarang, $tahun_terbit, $isbn, $stok, $cover_image, $id]);
            $success = "Buku berhasil diupdate!";
            
            // Refresh book data
            $stmt = $conn->prepare("SELECT * FROM buku WHERE id = ?");
            $stmt->execute([$id]);
            $book = $stmt->fetch();
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku - Sistem Perpustakaan</title>
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
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .form-control {
            border-radius: 10px;
            padding: 0.8rem 1rem;
            border: 1px solid #e0e0e0;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            background-color: #fff;
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
        .invalid-feedback {
            font-size: 0.85rem;
            color: #dc3545;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .book-info {
            background: rgba(26, 42, 108, 0.05);
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        .book-info-title {
            color: #1a2a6c;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
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
                        <a class="nav-link" href="list_buku.php">Daftar Buku</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="d-flex align-items-center mb-4" data-aos="fade-up">
                    <i class="fas fa-edit fa-2x me-3" style="color: #1a2a6c;"></i>
                    <h2 class="mb-0">Edit Buku</h2>
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
                        <div class="book-info">
                            <div class="book-info-title">
                                <i class="fas fa-info-circle me-2"></i>Data Buku Sekarang
                            </div>
                            <p class="mb-0">ID: <?php echo htmlspecialchars($book['id']); ?></p>
                        </div>

                        <form method="POST" class="needs-validation" novalidate enctype="multipart/form-data">
                            <div class="mb-4">
                                <label for="judul" class="form-label">
                                    <i class="fas fa-book"></i>Judul
                                </label>
                                <input type="text" class="form-control" id="judul" name="judul" 
                                       value="<?php echo htmlspecialchars($book['judul']); ?>" required>
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle"></i>
                                    Judul harus diisi
                                </div>
                            </div>                            <div class="mb-4">
                                <label for="pengarang" class="form-label">
                                    <i class="fas fa-user-edit"></i>Pengarang
                                </label>
                                <input type="text" class="form-control" id="pengarang" name="pengarang" 
                                       value="<?php echo htmlspecialchars($book['pengarang']); ?>" required>
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle"></i>
                                    Pengarang harus diisi
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="tahun_terbit" class="form-label">
                                        <i class="fas fa-calendar"></i>Tahun Terbit
                                    </label>
                                    <input type="text" class="form-control" id="tahun_terbit" name="tahun_terbit" 
                                           value="<?php echo htmlspecialchars($book['tahun_terbit']); ?>"
                                           pattern="\d{4}" title="Tahun terbit harus 4 digit" required>
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-circle"></i>
                                        Tahun terbit harus 4 digit
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label for="isbn" class="form-label">
                                        <i class="fas fa-barcode"></i>ISBN
                                    </label>
                                    <input type="text" class="form-control" id="isbn" name="isbn" 
                                           value="<?php echo htmlspecialchars($book['isbn']); ?>" required>
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-circle"></i>
                                        ISBN harus diisi
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="stok" class="form-label">
                                        <i class="fas fa-layer-group"></i>Stok
                                    </label>
                                    <input type="number" class="form-control" id="stok" name="stok" 
                                           value="<?php echo htmlspecialchars($book['stok']); ?>" min="0" required>
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-circle"></i>
                                        Stok harus diisi
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label for="cover_image" class="form-label">
                                        <i class="fas fa-image"></i>Cover Image
                                    </label>
                                    <input type="file" class="form-control" id="cover_image" 
                                           name="cover_image" accept="image/*">
                                    <div class="form-text">Format: JPG, PNG (Max 2MB)</div>                                    <?php if (isset($book['cover_image']) && !empty($book['cover_image'])): ?>
                                        <div class="mt-2">
                                            <img src="../../<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                                 alt="Current cover" class="img-thumbnail" style="height: 100px;">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update
                                </button>
                                <a href="list_buku.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali
                                </a>
                            </div>
                </form>
            </div>
        </div>
    </div>    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true
        });

        // Enhanced form validation
        (function () {
            'use strict'
            
            // Add floating labels animation
            const inputs = document.querySelectorAll('.form-control')
            inputs.forEach(input => {
                input.addEventListener('focus', () => {
                    input.style.transform = 'scale(1.01)';
                    input.style.transition = 'all 0.3s ease';
                })
                input.addEventListener('blur', () => {
                    input.style.transform = 'scale(1)';
                })

                if (input.value) {
                    input.parentElement.classList.add('filled')
                }
            })

            // Form validation with enhanced UI
            const form = document.querySelector('.needs-validation')
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                    
                    // Show error toast
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Silakan periksa kembali form isian Anda!',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    })
                } else {
                    // Show loading state
                    event.preventDefault()
                    Swal.fire({
                        title: 'Menyimpan...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading()
                        }
                    })
                    setTimeout(() => form.submit(), 1000)
                }
                form.classList.add('was-validated')
            }, false)

            // Add year picker enhancement
            const tahunInput = document.getElementById('tahun_terbit')
            tahunInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '')
                if (this.value.length > 4) {
                    this.value = this.value.slice(0, 4)
                }
            })

            // Add stok input enhancement
            const stokInput = document.getElementById('stok')
            stokInput.addEventListener('input', function() {
                if (this.value < 0) this.value = 0
            })
        })()
    </script>
</body>
</html>
