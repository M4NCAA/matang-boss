-- ============================================================
-- Matang Boss Database Schema v3.0
-- Platform: XAMPP / MySQL
-- ============================================================

CREATE DATABASE IF NOT EXISTS matangboss_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE matangboss_db;

-- ============================================================
-- Tabel: users
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id_user`    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama_lengkap` VARCHAR(100) NOT NULL,
    `username`   VARCHAR(50) NOT NULL UNIQUE,
    `email`      VARCHAR(100) NOT NULL UNIQUE,
    `password`   VARCHAR(255) NOT NULL COMMENT 'Hashed dengan password_hash()',
    `role`       ENUM('petani', 'admin', 'pemilik_ramp') NOT NULL DEFAULT 'petani',
    `foto_profil` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_user`),
    INDEX `idx_username` (`username`),
    INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Tabel: scan_history
-- ============================================================
CREATE TABLE IF NOT EXISTS `scan_history` (
    `id_scan`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_user`           INT UNSIGNED NOT NULL,
    `file_gambar`       VARCHAR(255) NOT NULL COMMENT 'Path relatif, contoh: uploads/scan_1234.jpg',
    `hasil_kematangan`  ENUM('Mentah', 'Matang', 'Lewat Matang') NOT NULL,
    `confidence_score`  DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Persentase akurasi AI, 0-100',
    `nama_penyakit`     VARCHAR(100) DEFAULT NULL COMMENT 'NULL jika buah sehat',
    `solusi_penyakit`   TEXT DEFAULT NULL,
    `latitude`          DECIMAL(10,8) DEFAULT NULL,
    `longitude`         DECIMAL(11,8) DEFAULT NULL,
    `timestamp`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_scan`),
    CONSTRAINT `fk_scan_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
    INDEX `idx_scan_user` (`id_user`),
    INDEX `idx_scan_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Tabel: ramp_directory
-- ============================================================
CREATE TABLE IF NOT EXISTS `ramp_directory` (
    `id_ramp`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_user`           INT UNSIGNED NOT NULL COMMENT 'FK ke pemilik RAMP',
    `nama_ramp`         VARCHAR(100) NOT NULL,
    `alamat_lengkap`    TEXT NOT NULL,
    `latitude`          DECIMAL(10,8) NOT NULL,
    `longitude`         DECIMAL(11,8) NOT NULL,
    `no_whatsapp`       VARCHAR(20) NOT NULL,
    `harga_jual`        INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Harga dalam Rupiah per KG',
    `status_approval`   ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `catatan_admin`     TEXT DEFAULT NULL,
    `last_updated`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_ramp`),
    CONSTRAINT `fk_ramp_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
    INDEX `idx_ramp_status` (`status_approval`),
    INDEX `idx_ramp_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Tabel: expert_rules (Sistem Pakar)
-- ============================================================
CREATE TABLE IF NOT EXISTS `expert_rules` (
    `id_rule`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `kode_penyakit`          VARCHAR(20) NOT NULL UNIQUE COMMENT 'Contoh: G001, B001',
    `gejala_atau_penyakit`   VARCHAR(100) NOT NULL,
    `deskripsi`              TEXT DEFAULT NULL,
    `rekomendasi_tindakan`   TEXT NOT NULL,
    `tingkat_bahaya`         ENUM('rendah', 'sedang', 'tinggi', 'kritis') NOT NULL DEFAULT 'sedang',
    PRIMARY KEY (`id_rule`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Data Awal (Seeding)
-- ============================================================

-- Admin default (password: admin123)
INSERT INTO `users` (`nama_lengkap`, `username`, `email`, `password`, `role`) VALUES
('Administrator', 'admin', 'admin@matangboss.id', '$2y$10$QsVI.OjUSZSJEKNRjYwCCevxvUHbMbZqx7GjJVPwyBeASuVDwq/ni', 'admin'),
('Demo Petani', 'petani', 'petani@matangboss.id', '$2y$10$QsVI.OjUSZSJEKNRjYwCCevxvUHbMbZqx7GjJVPwyBeASuVDwq/ni', 'petani');

-- Aturan sistem pakar
INSERT INTO `expert_rules` (`kode_penyakit`, `gejala_atau_penyakit`, `deskripsi`, `rekomendasi_tindakan`, `tingkat_bahaya`) VALUES
('G001', 'Ganoderma', 'Penyakit busuk pangkal batang yang disebabkan jamur Ganoderma boninense. Menyebabkan tandan buah segar berwarna tidak merata dan layu.', 'Segera isolasi pohon yang terinfeksi. Lakukan aplikasi fungisida berbahan aktif trifloxystrobin. Hubungi PPL terdekat. Gali parit isolasi 50cm di sekitar pohon.', 'kritis'),
('B001', 'Bercak Daun (Curvularia)', 'Infeksi jamur Curvularia yang menyebabkan bercak kecoklatan pada daun dengan tepi kuning.', 'Semprotkan fungisida berbasis mancozeb. Pangkas daun yang terinfeksi berat. Tingkatkan sirkulasi udara di kebun.', 'sedang'),
('U001', 'Ulat Api (Setothosea asigna)', 'Serangan ulat api menyebabkan kerusakan pada daun kelapa sawit, terlihat dari bekas gigitan di pinggir daun.', 'Aplikasikan insektisida berbahan aktif klorpirifos. Lakukan monitoring populasi mingguan. Gunakan agen hayati Beauveria bassiana.', 'tinggi'),
('K001', 'Kekurangan Magnesium', 'Daun tua berwarna oranye kekuningan mulai dari tepi, bukan karena kematangan.', 'Aplikasikan pupuk Kieserit (MgSO4) sebanyak 1-2 kg/pohon. Lakukan analisis tanah. Pantau respons selama 1-2 bulan.', 'rendah');

-- Contoh data RAMP
INSERT INTO `ramp_directory` (`id_user`, `nama_ramp`, `alamat_lengkap`, `latitude`, `longitude`, `no_whatsapp`, `harga_jual`, `status_approval`) VALUES
(1, 'RAMP Sumber Makmur', 'Jl. Lintas Timur Km. 12, Pelalawan, Riau', 0.50000000, 101.50000000, '6281234567890', 2450, 'approved'),
(1, 'PKS Inti Sawit Nusantara', 'Desa Suka Maju, Jalur 4, Indragiri Hulu', 0.50100000, 101.50200000, '6281398765432', 2400, 'approved');
