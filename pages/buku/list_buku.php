<?php
require_once '../../config/koneksi.php';
checkLoginSession();

// Pencarian
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = '';
if ($search) {
    $where = " WHERE judul LIKE :search OR pengarang LIKE :search";
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$start = ($page - 1) * $per_page;

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM buku" . $where);
if ($search) {
    $stmt->bindValue(':search', "%$search%");
}
$stmt->execute();
$total_rows = $stmt->fetch()['total'];
$total_pages = ceil($total_rows / $per_page);

$stmt = $conn->prepare("SELECT * FROM buku" . $where . " LIMIT :start, :per_page");
if ($search) {
    $stmt->bindValue(':search', "%$search%");
}
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
$stmt->execute();
$books = $stmt->fetchAll();

// Get user's active borrowings
$stmt = $conn->prepare("SELECT buku_id FROM peminjaman WHERE user_id = :user_id AND status = 'dipinjam'");
$stmt->bindValue(':user_id', $_SESSION['user_id']);
$stmt->execute();
$active_borrowings = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Buku - Sistem Perpustakaan</title>    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        }        .book-card {
            background: white;
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .book-cover {
            position: relative;
            padding-top: 140%;
            background: #f8f9fa;
            overflow: hidden;
        }

        .book-cover img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .book-card:hover .book-cover img {
            transform: scale(1.05);
        }

        .placeholder-cover {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1a2a6c;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        }

        .book-info {
            padding: 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .book-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1a2a6c;
            margin-bottom: 0.5rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .book-author {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .book-details {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 1rem;
        }

        .book-details p {
            margin-bottom: 0.5rem;
        }

        .genre-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            background: linear-gradient(135deg, #1a2a6c20, #b21f1f20);
            color: #1a2a6c;
            margin-bottom: 0.5rem;
        }

        .book-actions {
            margin-top: auto;
            display: flex;
            gap: 0.5rem;
        }
        .btn-primary {
            background: linear-gradient(45deg, #1a2a6c, #b21f1f);
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 42, 108, 0.2);
            background: linear-gradient(45deg, #15215a, #981b1b);
        }
        .btn-warning {
            background: linear-gradient(45deg, #f7b733, #fc4a1a);
            border: none;
            color: white;
        }
        .btn-danger {
            background: linear-gradient(45deg, #eb3349, #f45c43);
            border: none;
        }
        .pagination .page-link {
            border: none;
            color: #1a2a6c;
            margin: 0 5px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .pagination .page-item.active .page-link {
            background: linear-gradient(45deg, #1a2a6c, #b21f1f);
            border-color: transparent;
        }
        .search-box {
            position: relative;
        }
        .search-box .form-control {
            padding-left: 40px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .search-box .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }
        .book-title {
            font-weight: 500;
            color: #1a2a6c;
        }
        .stock-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .stock-available {
            background-color: #e3fcef;
            color: #00875a;
        }
        .stock-low {
            background-color: #fff4e5;
            color: #b76e00;
        }
        .stock-empty {
            background-color: #ffe8e8;
            color: #dc3545;
        }
        .modal-content {
            background: #fff;
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .modal-header {
            border-bottom: none;
            padding: 1.5rem;
            background: linear-gradient(135deg, #1a2a6c, #b21f1f);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        .modal-body {
            padding: 1.5rem;
        }
        .modal-footer {
            border-top: none;
            padding: 1.5rem;
        }
        .btn-close {
            color: white;
            opacity: 1;
            filter: brightness(0) invert(1);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Override Bootstrap modal backdrop */
        .modal-backdrop {
            display: none;
        }
        
        .modal {
            background: rgba(0, 0, 0, 0.5);
            padding-right: 0 !important;
        }
        
        body.modal-open {
            overflow: auto !important;
            padding-right: 0 !important;
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
                        <a class="nav-link active" href="list_buku.php">Daftar Buku</a>
                    </li>
                    <?php if (isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="tambah_buku.php">Tambah Buku</a>
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
        <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
            <div>
                <h2><i class="fas fa-books me-2"></i>Daftar Buku</h2>
                <p class="text-muted">Total <?php echo $total_rows; ?> buku tersedia</p>
            </div>
            <?php if (isAdmin()): ?>
            <a href="tambah_buku.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Tambah Buku
            </a>
            <?php endif; ?>
        </div>

        <div class="card mb-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Cari judul atau pengarang..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Cari
                        </button>
                    </div>
                </form>
            </div>
        </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4" data-aos="fade-up" data-aos-delay="200">
        <?php foreach ($books as $index => $book): ?>
        <div class="col">
            <div class="book-card">
                <div class="book-cover">
                    <?php if (!empty($book['cover_image'])): ?>                        <img src="../../<?php echo htmlspecialchars($book['cover_image']); ?>" alt="<?php echo htmlspecialchars($book['judul']); ?>" class="img-fluid">
                    <?php else: ?>
                        <div class="placeholder-cover">
                            <i class="fas fa-book fa-3x"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="book-info">
                    <h5 class="book-title"><?php echo htmlspecialchars($book['judul']); ?></h5>
                    <p class="book-author">
                        <i class="fas fa-user-edit me-2"></i>
                        <?php echo htmlspecialchars($book['pengarang']); ?>
                    </p>
                    <div class="book-details">
                        <p><i class="fas fa-building me-2"></i><?php echo htmlspecialchars($book['penerbit']); ?></p>
                        <p><i class="fas fa-calendar me-2"></i><?php echo htmlspecialchars($book['tahun_terbit']); ?></p>
                        <span class="genre-badge">
                            <i class="fas fa-bookmark me-2"></i><?php echo htmlspecialchars($book['genre']); ?>
                        </span>
                        <?php
                        $stok = (int)$book['stok'];
                        $stockClass = $stok > 5 ? 'stock-available' : ($stok > 0 ? 'stock-low' : 'stock-empty');
                        ?>
                        <span class="stock-badge <?php echo $stockClass; ?>">
                            <i class="fas fa-books me-2"></i><?php echo $stok; ?> buku
                        </span>
                    </div>
                    <div class="book-actions">
                        <?php if (!isAdmin()): ?>
                            <?php if ($book['stok'] > 0): ?>
                                <?php if (!in_array($book['id'], $active_borrowings)): ?>
                                <button type="button" class="btn btn-primary w-100 btn-pinjam" 
                                    data-buku-id="<?php echo $book['id']; ?>"
                                    data-judul="<?php echo htmlspecialchars($book['judul']); ?>"
                                    data-stok="<?php echo $book['stok']; ?>">
                                    <i class="fas fa-book-reader me-2"></i>Pinjam
                                </button>
                                <?php else: ?>
                                <button class="btn btn-secondary w-100" disabled>
                                    <i class="fas fa-clock me-2"></i>Sedang Dipinjam
                                </button>
                                <?php endif; ?>
                            <?php else: ?>
                            <button class="btn btn-secondary w-100" disabled>
                                <i class="fas fa-book-reader me-2"></i>Stok Habis
                            </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="d-flex gap-2 w-100">
                                <a href="edit_buku.php?id=<?php echo $book['id']; ?>" 
                                   class="btn btn-warning flex-grow-1">
                                    <i class="fas fa-edit me-2"></i>Edit
                                </a>
                                <button class="btn btn-danger flex-grow-1" 
                                        onclick="konfirmasiHapus(<?php echo $book['id']; ?>)">
                                    <i class="fas fa-trash me-2"></i>Hapus
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>        </div>
        <?php endforeach; ?>

        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>        <!-- Peminjaman Modal -->
        <div class="modal fade" id="pinjamModal" tabindex="-1" aria-labelledby="pinjamModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="pinjamModalLabel">Pinjam Buku</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="pinjamForm">
                        <div class="modal-body">
                            <input type="hidden" id="buku_id" name="buku_id">
                            <div class="mb-3">
                                <label class="form-label">Judul Buku</label>
                                <input type="text" class="form-control" id="judul_buku" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jumlah Pinjam</label>
                                <input type="number" class="form-control" id="jumlah" name="jumlah" min="1" value="1" required>
                                <div class="form-text">Stok tersedia: <span id="stok_tersedia"></span></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Pinjam</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize AOS
            AOS.init({
                duration: 800,
                once: true
            });

            // Initialize Modal
            const pinjamModal = document.getElementById('pinjamModal');
            const modalInstance = new bootstrap.Modal(pinjamModal);
            const pinjamForm = document.getElementById('pinjamForm');
            const jumlahInput = document.getElementById('jumlah');
            const stokTersediaSpan = document.getElementById('stok_tersedia');
            
            // Handle borrow button click
            document.querySelectorAll('.btn-pinjam').forEach(button => {
                button.addEventListener('click', function() {
                    const bukuId = this.dataset.bukuId;
                    const judul = this.dataset.judul;
                    const stok = this.dataset.stok;
                    
                    document.getElementById('buku_id').value = bukuId;
                    document.getElementById('judul_buku').value = judul;
                    stokTersediaSpan.textContent = stok;
                    jumlahInput.max = stok;
                    jumlahInput.value = "1";
                    
                    modalInstance.show();
                });
            });

            // Handle form submission
            pinjamForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const bukuId = document.getElementById('buku_id').value;
                const jumlahPinjam = parseInt(jumlahInput.value);
                const stokTersedia = parseInt(stokTersediaSpan.textContent);

                // Validasi input
                if (!bukuId || isNaN(jumlahPinjam) || jumlahPinjam < 1) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Data tidak valid'
                    });
                    return;
                }

                if (jumlahPinjam > stokTersedia) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Jumlah pinjam melebihi stok tersedia'
                    });
                    return;
                }

                try {
                    modalInstance.hide();

                    // Show loading
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Send request
                    const formData = new FormData();
                    formData.append('buku_id', bukuId);
                    formData.append('jumlah', jumlahPinjam);
                    formData.append('action', 'pinjam');

                    const response = await fetch('peminjaman_proses.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        await Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: result.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        location.reload();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message
                        });
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan, silakan coba lagi'
                    });
                }
            });

            // Handle modal close
            pinjamModal.addEventListener('hidden.bs.modal', function() {
                pinjamForm.reset();
                stokTersediaSpan.textContent = '';
            });

            // Validate jumlah input
            jumlahInput.addEventListener('input', function() {
                const stokTersedia = parseInt(stokTersediaSpan.textContent);
                if (this.value < 1) this.value = 1;
                if (this.value > stokTersedia) this.value = stokTersedia;
            });
        });
    </script>
</body>
</html>
