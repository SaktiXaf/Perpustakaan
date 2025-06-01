<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'perpustakaan_nama';

try {
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Maaf, terjadi kesalahan koneksi ke database. Silakan coba beberapa saat lagi.");
}

// Create peminjaman table if not exists
try {
    $sql = "CREATE TABLE IF NOT EXISTS peminjaman (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        buku_id INT NOT NULL,
        jumlah INT NOT NULL DEFAULT 1,
        tanggal_pinjam DATE NOT NULL,
        tanggal_kembali DATE NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'dipinjam',
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (buku_id) REFERENCES buku(id)
    )";
    $conn->exec($sql);

    // Add cover_image column to buku table if not exists
    $sql = "SHOW COLUMNS FROM buku LIKE 'cover_image'";
    $result = $conn->query($sql);
    if ($result->rowCount() == 0) {
        $sql = "ALTER TABLE buku ADD COLUMN cover_image VARCHAR(255) DEFAULT NULL";
        $conn->exec($sql);
    }
} catch(PDOException $e) {
    // Silently handle table creation errors
    error_log("Table creation error: " . $e->getMessage());
}

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function checkLoginSession() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /perpustakaan/login.php");
        exit();
    }
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
?>
