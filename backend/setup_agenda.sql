-- Jalankan query ini di phpMyAdmin atau MySQL CLI
-- Database: db_ipbmengajar

CREATE TABLE IF NOT EXISTS `agenda_kegiatan` (
  `id`            INT           NOT NULL AUTO_INCREMENT,
  `tanggal_sort`  DATE          NOT NULL COMMENT 'Dipakai untuk ORDER BY ASC',
  `teks_tanggal`  VARCHAR(100)  NOT NULL DEFAULT '' COMMENT 'Teks yang ditampilkan ke user, misal: "24 Mei 2026" atau "TBA"',
  `judul`         VARCHAR(255)  NOT NULL,
  `deskripsi`     TEXT          NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data agenda resmi IPB Mengajar
INSERT INTO `agenda_kegiatan` (`tanggal_sort`, `teks_tanggal`, `judul`, `deskripsi`) VALUES
('2026-04-19', '19 April 2026',    'Opening IM - Blinks',                   'Pembukaan rangkaian kegiatan IM - Blinks.'),
('2026-04-25', '25 April 2026',    'IM - Blinks Day 1',                     'Hari pertama pelaksanaan program IM - Blinks.'),
('2026-04-29', '29 - 30 April 2026', 'Opsi #1 Temu Lentera',               'Pilihan pelaksanaan agenda Temu Lentera.'),
('2026-05-03', '3 - 4 Mei 2026',   'Oprec Pengajar Inspiratif',            'Pembukaan rekrutmen untuk calon Pengajar Inspiratif.'),
('2026-05-10', '10 - 11 Mei 2026', 'Oprec Pengajar Inspiratif',            'Lanjutan rekrutmen untuk calon Pengajar Inspiratif.'),
('2026-05-17', '17 Mei 2026',      'Tes TPU dan Psikotes PI',               'Tahapan seleksi tes bagi calon Pengajar Inspiratif.'),
('2026-05-23', '23 Mei 2026',      'IM - Blinks',                           'Pelaksanaan program lanjutan IM - Blinks.'),
('2026-05-24', '24 Mei 2026',      'Wawancara PI',                          'Sesi wawancara bagi calon Pengajar Inspiratif.'),
('2026-06-06', '6 Juni 2026',      'Opsi IM - Makrab / Mini Project PI',    'Agenda malam keakraban atau pengerjaan mini project PI.'),
('2099-12-31', 'TBA',              'Rangkaian Kegiatan Selanjutnya',        'Agenda berikutnya akan segera diumumkan. Nantikan info terbarunya!');

