-- Database Schema untuk Sistem Monitoring Pakan Ikan Otomatis
-- Database: sql_kel5_myiot_fun

-- Table: users (untuk login admin)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `nama_lengkap` VARCHAR(100),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (password: admin123)
INSERT INTO `users` (`username`, `password`, `nama_lengkap`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator');

-- Table: sensor_data (data real-time dari ESP32)
CREATE TABLE IF NOT EXISTS `sensor_data` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `suhu_air` FLOAT NOT NULL,
  `jenis_ikan` VARCHAR(50),
  `umur_ikan` INT,
  `jumlah_ikan` INT,
  `pakan_per_bukaan` FLOAT,
  `protein_percent` FLOAT,
  `lemak_percent` FLOAT,
  `serat_percent` FLOAT,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: ml_predictions (output dari ML Python)
CREATE TABLE IF NOT EXISTS `ml_predictions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `rekomendasi_pakan` FLOAT NOT NULL,
  `frekuensi_pakan` INT NOT NULL,
  `waktu_pakan` TEXT,
  `bukaan_per_jadwal` INT,
  `input_jumlah_ikan` INT,
  `input_umur_ikan` INT,
  `input_pakan_per_bukaan` FLOAT,
  `input_protein` FLOAT,
  `input_lemak` FLOAT,
  `input_serat` FLOAT,
  `input_suhu` FLOAT,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: log_aktivitas (semua aktivitas feeding)
CREATE TABLE IF NOT EXISTS `log_aktivitas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `waktu_eksekusi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `sumber` VARCHAR(50) NOT NULL COMMENT 'Manual/ML/Auto V7/Manual Jadwal',
  `bukaan_servo` INT NOT NULL,
  `suhu_air` FLOAT,
  `pesan` TEXT,
  INDEX `idx_waktu` (`waktu_eksekusi`),
  INDEX `idx_sumber` (`sumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: system_status (status real-time sistem)
CREATE TABLE IF NOT EXISTS `system_status` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `mode_auto_v7` BOOLEAN DEFAULT FALSE,
  `mode_ml` BOOLEAN DEFAULT FALSE,
  `esp32_connected` BOOLEAN DEFAULT FALSE,
  `last_feeding` TIMESTAMP NULL,
  `next_feeding_time` VARCHAR(10),
  `next_feeding_source` VARCHAR(50),
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default system status
INSERT INTO `system_status` (`mode_auto_v7`, `mode_ml`, `esp32_connected`) VALUES
(FALSE, FALSE, FALSE);

-- Table: manual_schedules (jadwal manual 4 slot)
CREATE TABLE IF NOT EXISTS `manual_schedules` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slot_number` INT NOT NULL COMMENT '1-4',
  `jam` INT,
  `menit` INT,
  `aktif` BOOLEAN DEFAULT FALSE,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_slot` (`slot_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert 4 manual schedule slots
INSERT INTO `manual_schedules` (`slot_number`, `jam`, `menit`, `aktif`) VALUES
(1, NULL, NULL, FALSE),
(2, NULL, NULL, FALSE),
(3, NULL, NULL, FALSE),
(4, NULL, NULL, FALSE);

-- Table: feeding_history_daily (agregasi harian untuk grafik)
CREATE TABLE IF NOT EXISTS `feeding_history_daily` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tanggal` DATE NOT NULL,
  `total_feeding` INT DEFAULT 0,
  `total_bukaan` INT DEFAULT 0,
  `avg_suhu` FLOAT,
  UNIQUE KEY `unique_date` (`tanggal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Stored Procedure: Update daily statistics
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS update_daily_stats()
BEGIN
  INSERT INTO feeding_history_daily (tanggal, total_feeding, total_bukaan, avg_suhu)
  SELECT 
    DATE(waktu_eksekusi) as tanggal,
    COUNT(*) as total_feeding,
    SUM(bukaan_servo) as total_bukaan,
    AVG(suhu_air) as avg_suhu
  FROM log_aktivitas
  WHERE DATE(waktu_eksekusi) = CURDATE()
  GROUP BY DATE(waktu_eksekusi)
  ON DUPLICATE KEY UPDATE
    total_feeding = VALUES(total_feeding),
    total_bukaan = VALUES(total_bukaan),
    avg_suhu = VALUES(avg_suhu);
END$$
DELIMITER ;

-- Create views for easier querying
CREATE OR REPLACE VIEW v_latest_sensor AS
SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 1;

CREATE OR REPLACE VIEW v_latest_ml_prediction AS
SELECT * FROM ml_predictions ORDER BY timestamp DESC LIMIT 1;

CREATE OR REPLACE VIEW v_today_feeding_count AS
SELECT COUNT(*) as count, SUM(bukaan_servo) as total_bukaan
FROM log_aktivitas 
WHERE DATE(waktu_eksekusi) = CURDATE();

-- 🔥 Tabel untuk input ML sementara (dari web)
CREATE TABLE IF NOT EXISTS `temporary_ml_input` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `jenis_ikan` VARCHAR(50) NOT NULL,
  `umur_ikan` INT NOT NULL,
  `jumlah_ikan` INT NOT NULL,
  `pakan_per_bukaan` FLOAT NOT NULL,
  `protein_percent` FLOAT NOT NULL,
  `lemak_percent` FLOAT NOT NULL,
  `serat_percent` FLOAT NOT NULL,
  `suhu_air` FLOAT NOT NULL,
  `status` ENUM('pending','processed') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 🔥 Tambah kolom input_id di ml_predictions (untuk relasi)
ALTER TABLE `ml_predictions` 
ADD COLUMN `input_id` INT NULL DEFAULT NULL AFTER `id`,
ADD INDEX `idx_input_id` (`input_id`);

-- 🔥 View untuk dashboard tetap aman
CREATE OR REPLACE VIEW v_latest_ml_prediction AS
SELECT * FROM ml_predictions ORDER BY timestamp DESC LIMIT 1;