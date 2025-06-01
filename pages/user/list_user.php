<?php
require_once '../../config/koneksi.php';
checkLoginSession();

if (!isAdmin()) {
    header("Location: ../dashboard.php");
    exit();
}

// Pencarian
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = '';
if ($search) {
    $where = " WHERE username LIKE :search OR role LIKE :search";
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$start = ($page - 1) * $per_page;

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM users" . $where);
if ($search) {
    $stmt->bindValue(':search', "%$search%");
}
$stmt->execute();
$total_rows = $stmt->fetch()['total'];
$total_pages = ceil($total_rows / $per_page);

$stmt = $conn->prepare("SELECT * FROM users" . $where . " LIMIT :start, :per_page");
if ($search) {
    $stmt->bindValue(':search', "%$search%");
}
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Sistem Perpustakaan</title>
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
        .table {
            margin-bottom: 0;
        }
        .table th {
            font-weight: 600;
            color: #1a2a6c;
            border-bottom-width: 2px;
        }
        .table td {
            vertical-align: middle;
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
        .role-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .role-admin {
            background: #e3fcef;
            color: #00875a;
        }
        .role-user {
            background: #fff4e5;
            color: #b76e00;
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
        <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
            <div>
                <h2><i class="fas fa-users me-2"></i>Kelola User</h2>
                <p class="text-muted">Total <?php echo $total_rows; ?> user terdaftar</p>
            </div>
            <a href="tambah_user.php" class="btn btn-primary">
                <i class="fas fa-user-plus me-2"></i>Tambah User
            </a>
        </div>

        <div class="card mb-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Cari username atau role..." 
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

        <div class="table-responsive" data-aos="fade-up" data-aos-delay="200">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $index => $user): ?>
                    <tr class="align-middle">
                        <td><?php echo $start + $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td>
                            <?php
                            $roleClass = $user['role'] === 'admin' ? 'role-admin' : 'role-user';
                            ?>
                            <span class="role-badge <?php echo $roleClass; ?>">
                                <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" 
                                   class="btn btn-sm btn-warning me-1">
                                    <i class="fas fa-edit me-1"></i>Edit
                                </a>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <button class="btn btn-sm btn-danger" 
                                        onclick="konfirmasiHapus(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                    <i class="fas fa-trash me-1"></i>Hapus
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

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
        <?php endif; ?>
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

        function konfirmasiHapus(id, username) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: `Apakah Anda yakin ingin menghapus user "${username}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `hapus_user.php?id=${id}`;
                }
            });
        }

        // Add hover effect to table rows
        document.querySelectorAll('tbody tr').forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f8f9fa';
                this.style.transition = 'all 0.3s ease';
            });
            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });

        // Add animation to search results
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('search')) {
            document.querySelectorAll('tbody tr').forEach(row => {
                row.style.animation = 'fadeIn 0.5s ease-out';
            });
        }
    </script>

    <style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    </style>
</body>
</html>
