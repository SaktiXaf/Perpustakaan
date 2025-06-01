<?php
require_once '../config/koneksi.php';
checkLoginSession();

// Get total books
$stmt = $conn->query("SELECT COUNT(*) as total FROM buku");
$total_books = $stmt->fetch()['total'];

// Get active borrowings
if (isAdmin()) {    $stmt = $conn->prepare("
        SELECT p.id, p.buku_id, p.user_id, p.tanggal_pinjam, p.status, p.jumlah, b.judul, u.username 
        FROM peminjaman p 
        JOIN buku b ON p.buku_id = b.id 
        JOIN users u ON p.user_id = u.id 
        WHERE p.status = 'dipinjam'
        ORDER BY p.tanggal_pinjam DESC
    ");
} else {    $stmt = $conn->prepare("
        SELECT p.id, p.buku_id, p.user_id, p.tanggal_pinjam, p.status, p.jumlah, b.judul 
        FROM peminjaman p 
        JOIN buku b ON p.buku_id = b.id 
        WHERE p.user_id = :user_id AND p.status = 'dipinjam'
        ORDER BY p.tanggal_pinjam DESC
    ");
    $stmt->bindValue(':user_id', $_SESSION['user_id']);
}
$stmt->execute();
$active_borrowings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Perpustakaan</title>
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
            background: white;
            overflow: hidden;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .card-body {
            padding: 2rem;
        }
        .card-title {
            color: #1a2a6c;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }
        .display-4 {
            font-weight: 700;
            color: #b21f1f;
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
        }
        .btn-primary {
            background: linear-gradient(45deg, #1a2a6c, #b21f1f);
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 42, 108, 0.2);
            background: linear-gradient(45deg, #15215a, #981b1b);
        }
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, #1a2a6c, #b21f1f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        h2 {
            color: #1a2a6c;
            font-weight: 600;
            margin-bottom: 2rem;
        }
        .welcome-section {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem;
        }
        .welcome-text {
            font-size: 1.2rem;
            color: #666;
            margin-top: 1rem;
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
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="buku/list_buku.php">Daftar Buku</a>
                    </li>
                    <?php if (isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="buku/tambah_buku.php">Tambah Buku</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="user/list_user.php">Kelola User</a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="nav-link">Welcome, <?php echo $_SESSION['username']; ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>    <div class="container mt-4">
        <div class="welcome-section" data-aos="fade-up">
            <h2><i class="fas fa-books"></i> Dashboard</h2>
            <p class="welcome-text">Selamat datang di Sistem Perpustakaan Digital</p>
        </div>
        
        <div class="row mt-4 g-4">
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-book stat-icon"></i>
                        <h5 class="card-title">Total Buku</h5>
                        <p class="card-text display-4"><?php echo $total_books; ?></p>
                    </div>
                </div>
            </div>
            
            <?php if (isAdmin()): ?>
            <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-book-reader stat-icon"></i>
                        <h5 class="card-title">Peminjaman Aktif</h5>
                        <p class="card-text display-4"><?php echo count($active_borrowings); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Active Borrowings Section -->
        <div class="card mt-4" data-aos="fade-up">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-book-reader me-2"></i>
                    <?php echo isAdmin() ? 'Semua Peminjaman Aktif' : 'Peminjaman Aktif Anda'; ?>
                </h5>
                
                <?php if (empty($active_borrowings)): ?>
                    <p class="text-muted">Tidak ada peminjaman aktif saat ini.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Judul Buku</th>
                                    <?php if (isAdmin()): ?>
                                        <th>Peminjam</th>
                                    <?php endif; ?>
                                    <th>Tanggal Pinjam</th>
                                    <th>Jumlah</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($active_borrowings as $pinjam): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($pinjam['judul']); ?></td>
                                        <?php if (isAdmin()): ?>
                                            <td><?php echo htmlspecialchars($pinjam['username']); ?></td>
                                        <?php endif; ?>
                                        <td><?php echo date('d/m/Y', strtotime($pinjam['tanggal_pinjam'])); ?></td>
                                        <td><?php echo $pinjam['jumlah']; ?> buku</td>
                                        <td>
                                            <button class="btn btn-success btn-sm" onclick='kembalikanBuku(<?php
                                                echo json_encode([
                                                    'id' => $pinjam['id'],
                                                    'judul' => $pinjam['judul'],
                                                    'buku_id' => $pinjam['buku_id'],
                                                    'jumlah' => $pinjam['jumlah']
                                                ], JSON_HEX_APOS | JSON_HEX_QUOT);
                                            ?>)'>
                                                <i class="fas fa-undo-alt me-1"></i>Kembalikan
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Add SweetAlert2 and return book functionality -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            function kembalikanBuku(pinjam) {
                console.log('Data peminjaman:', pinjam); // Debug info
                
                Swal.fire({
                    title: 'Konfirmasi Pengembalian',
                    text: `Apakah Anda yakin ingin mengembalikan buku "${pinjam.judul}"?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Kembalikan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('pinjam_id', pinjam.id);
                        formData.append('buku_id', pinjam.buku_id);
                        formData.append('jumlah', pinjam.jumlah || 1); // default to 1 if not set
                        formData.append('action', 'kembali');

                        // Show loading indicator
                        Swal.fire({
                            title: 'Memproses Pengembalian...',
                            text: 'Mohon tunggu sebentar',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Send request
                        fetch('buku/peminjaman_proses.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: data.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: data.message
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Terjadi kesalahan saat mengembalikan buku'
                            });
                        });
                    }
                });
            }
        </script>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS animation library
        AOS.init({
            duration: 800,
            once: true
        });

        // Add smooth hover effect to cards
        document.querySelectorAll('.card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Add active class to current nav item
        document.querySelectorAll('.nav-link').forEach(link => {
            if (link.getAttribute('href') === window.location.pathname.split('/').pop()) {
                link.classList.add('active');
            }
        });

        // Add number counter animation
        function animateValue(obj, start, end, duration) {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                obj.innerHTML = Math.floor(progress * (end - start) + start);
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }

        // Animate numbers when they come into view
        document.querySelectorAll('.display-4').forEach(counter => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateValue(counter, 0, parseInt(counter.innerHTML), 1500);
                        observer.unobserve(entry.target);
                    }
                });
            });
            observer.observe(counter);
        });
    </script>
</body>
</html>
