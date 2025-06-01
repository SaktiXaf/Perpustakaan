-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 31 Bulan Mei 2025 pada 15.12
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `perpustakaan_nama`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `buku`
--

CREATE TABLE `buku` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `pengarang` varchar(255) NOT NULL,
  `penerbit` varchar(255) NOT NULL,
  `tahun_terbit` year(4) NOT NULL,
  `isbn` varchar(13) NOT NULL,
  `genre` varchar(100) NOT NULL,
  `stok` int(11) NOT NULL,
  `cover_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `buku`
--

INSERT INTO `buku` (`id`, `judul`, `pengarang`, `penerbit`, `tahun_terbit`, `isbn`, `genre`, `stok`, `cover_image`) VALUES
(1, 'Naruto Shippuden', ' Masashi Kishimoto', 'Prentice Hall', '2008', '9780132350884', 'Programming', 7, 'assets/book_covers/683ac5bf5aec0_naruto.jpeg'),
(2, 'Jujutsu kaisen', 'Gege Akutami', 'Addison-Wesley', '2008', '9780201633610', 'Software Engineering', 17, 'assets/book_covers/683ac61d6212c_jujutsu kaisenn.jpeg'),
(4, 'One Piece', 'Eiichiro Oda', 'sakti manga', '2008', '123749687894', 'Fiksi', 20, 'assets/book_covers/683ac65d681ff_one piece.jpeg'),
(5, 'Captain Tsubasa', ' Yōichi Takahashi', 'sueisha', '2008', '', 'Novel', 19, 'assets/book_covers/683ac6a63b93b_tsubasa.jpeg'),
(6, 'Blue Lock', 'Muneyuki Kaneshiro', ' Weekly Shōnen Magazine', '2008', '', 'Fiksi', 10, 'assets/book_covers/683ad56ca130e_blue lock.jpeg'),
(7, 'Attack On Titan', 'Hajime Isayama', 'Hajime Isayama', '2008', '', 'Fiksi', 15, 'assets/book_covers/683ad6af5c4ff_aot.jpeg'),
(8, 'Haikyuu', 'Haruichi Furudate', 'Shueisha', '2008', '', 'Fiksi', 26, 'assets/book_covers/683afb870947f_haikyuu.jpeg'),
(9, 'Hunter x Hunter', 'Yoshihiro Togashi', 'Shueisha', '2008', '', 'Fiksi', 17, 'assets/book_covers/683afbd97fa1d_hxh.jpeg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `buku_id` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL DEFAULT 1,
  `tanggal_pinjam` date NOT NULL,
  `tanggal_kembali` date DEFAULT NULL,
  `status` enum('dipinjam','dikembalikan') NOT NULL DEFAULT 'dipinjam'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `peminjaman`
--

INSERT INTO `peminjaman` (`id`, `user_id`, `buku_id`, `jumlah`, `tanggal_pinjam`, `tanggal_kembali`, `status`) VALUES
(1, 2, 1, 2, '2025-05-30', '2025-05-30', 'dikembalikan'),
(2, 2, 2, 2, '2025-05-31', NULL, 'dipinjam'),
(3, 2, 1, 1, '2025-05-31', '2025-05-31', 'dikembalikan'),
(4, 2, 4, 3, '2025-05-31', '2025-05-31', 'dikembalikan'),
(5, 2, 1, 3, '2025-05-31', NULL, 'dipinjam');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
(2, 'sanzz', '$2y$10$/ysSHiybeKF3gh1Ev52VPus0ChG1DSf3atAEu18tuak7q3G6C0coC', 'user');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `buku_id` (`buku_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `buku`
--
ALTER TABLE `buku`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `peminjaman_ibfk_2` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
