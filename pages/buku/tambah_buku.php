<?php
require_once '../../config/koneksi.php';
checkLoginSession();

if (!isAdmin()) {
    header("Location: list_buku.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'] ?? '';
    $pengarang = $_POST['pengarang'] ?? '';
    $penerbit = $_POST['penerbit'] ?? '';
    $tahun_terbit = $_POST['tahun_terbit'] ?? '';
    $genre = $_POST['genre'] ?? '';
    $stok = $_POST['stok'] ?? '';

    // Handle image upload
    $cover_image = '';
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['cover_image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            $error = "Format file harus JPG atau PNG!";
        } else {
            $fileName = uniqid() . '_' . basename($file['name']);
            $uploadPath = '../../assets/book_covers/' . $fileName;
            
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $cover_image = 'assets/book_covers/' . $fileName;
            } else {
                $error = "Gagal mengupload gambar!";
            }
        }
    }

    // Validasi
    if (empty($judul) || empty($pengarang) || empty($penerbit) || empty($tahun_terbit) || empty($genre) || empty($stok)) {
        $error = "Semua field harus diisi!";
    } elseif (!preg_match("/^\d{4}$/", $tahun_terbit)) {
        $error = "Format tahun terbit harus 4 digit!";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO buku (judul, pengarang, penerbit, tahun_terbit, genre, stok, cover_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$judul, $pengarang, $penerbit, $tahun_terbit, $genre, $stok, $cover_image]);
            $success = "Buku berhasil ditambahkan!";
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
    <title>Tambah Buku - Sistem Perpustakaan</title>
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
        }
        .form-floating {
            position: relative;
        }
        .input-icon {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            left: 1rem;
            color: #6c757d;
        }
        .input-with-icon {
            padding-left: 2.5rem;
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
                    <li class="nav-item">
                        <a class="nav-link active" href="tambah_buku.php">Tambah Buku</a>
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
                    <i class="fas fa-book-medical fa-2x me-3" style="color: #1a2a6c;"></i>
                    <h2 class="mb-0">Tambah Buku</h2>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger" data-aos="fade-up">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success" data-aos="fade-up">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <div class="card" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-body">
                        <form method="POST" class="needs-validation" novalidate enctype="multipart/form-data">
                            <div class="mb-4">
                                <label for="judul" class="form-label">
                                    <i class="fas fa-book me-2"></i>Judul
                                </label>
                                <input type="text" class="form-control" 
                                       id="judul" name="judul" required
                                       placeholder="Masukkan judul buku">
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    Judul harus diisi
                                </div>
                            </div>                            <div class="mb-4">
                                <label for="pengarang" class="form-label">
                                    <i class="fas fa-user-edit me-2"></i>Pengarang
                                </label>
                                <input type="text" class="form-control" 
                                       id="pengarang" name="pengarang" required
                                       placeholder="Masukkan nama pengarang">
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    Pengarang harus diisi
                                </div>
                            </div>                            <div class="mb-4">
                                <label for="penerbit" class="form-label">
                                    <i class="fas fa-building me-2"></i>Penerbit
                                </label>
                                <input type="text" class="form-control" 
                                       id="penerbit" name="penerbit" required
                                       placeholder="Masukkan nama penerbit">
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    Penerbit harus diisi
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="tahun_terbit" class="form-label">
                                        <i class="fas fa-calendar me-2"></i>Tahun Terbit
                                    </label>
                                    <input type="text" class="form-control" 
                                           id="tahun_terbit" name="tahun_terbit"
                                           pattern="\d{4}" title="Tahun terbit harus 4 digit" 
                                           placeholder="YYYY" required>
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        Tahun terbit harus 4 digit
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label for="genre" class="form-label">
                                        <i class="fas fa-tags me-2"></i>Genre
                                    </label>
                                    <select class="form-control" id="genre" name="genre" required>
                                        <option value="">Pilih Genre</option>
                                        <option value="Fiksi">Fiksi</option>
                                        <option value="Non-Fiksi">Non-Fiksi</option>
                                        <option value="Novel">Novel</option>
                                        <option value="Pendidikan">Pendidikan</option>
                                        <option value="Teknologi">Teknologi</option>
                                        <option value="Sains">Sains</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        Genre harus dipilih
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="stok" class="form-label">
                                    <i class="fas fa-layer-group me-2"></i>Stok
                                </label>
                                <input type="number" class="form-control" 
                                       id="stok" name="stok" min="0" required
                                       placeholder="Jumlah stok">
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    Stok harus diisi
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="cover_image" class="form-label">
                                    <i class="fas fa-image me-2"></i>Cover Image
                                </label>
                                <input type="file" class="form-control" 
                                       id="cover_image" name="cover_image" accept="image/*">
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    Gambar sampul harus diunggah
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan
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

        // Form validation with enhanced UI
        (function () {
            'use strict'
            
            // Add floating labels animation
            const inputs = document.querySelectorAll('.form-control')
            inputs.forEach(input => {
                input.addEventListener('focus', () => {
                    input.parentElement.classList.add('focused')
                })
                input.addEventListener('blur', () => {
                    if (!input.value) {
                        input.parentElement.classList.remove('focused')
                    }
                })
            })

            // Enhanced form validation
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

            // Add form input animations
            document.querySelectorAll('.form-control, .form-select').forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.transform = 'scale(1.02)'
                    this.style.transition = 'all 0.3s ease'
                })
                input.addEventListener('blur', function() {
                    this.style.transform = 'scale(1)'
                })
            })
        })()
    </script>
</body>
</html>
