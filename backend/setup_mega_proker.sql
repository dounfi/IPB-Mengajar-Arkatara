-- Jalankan di phpMyAdmin → database: db_ipbmengajar

CREATE TABLE IF NOT EXISTS `mega_proker` (
  `id`           INT           NOT NULL AUTO_INCREMENT,
  `urutan`       INT           NOT NULL DEFAULT 0 COMMENT 'Untuk mengatur urutan tampil',
  `nama_proker`  VARCHAR(100)  NOT NULL,
  `subtitle`     VARCHAR(200)  NOT NULL DEFAULT '',
  `deskripsi`    TEXT          NOT NULL,
  `accent`       VARCHAR(20)   NOT NULL DEFAULT 'green' COMMENT 'Nilai: green|blue|yellow|teal|amber',
  `grad`         VARCHAR(200)  NOT NULL DEFAULT 'from-green-light via-green-medium to-green-dark',
  `file_gambar`  VARCHAR(255)  NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed data dari proker yang sudah ada
INSERT INTO `mega_proker` (`urutan`, `nama_proker`, `subtitle`, `deskripsi`, `accent`, `grad`, `file_gambar`) VALUES
(1, 'EDELWEIS', 'Education Weeks in School',
 'Edelweis (Education Weeks in School) merupakan salah satu mega proker dari IPB Mengajar yang menghadirkan pengajar inspiratif untuk berkontribusi di desa binaan selama 21 hari. Program ini bertujuan untuk memberikan dampak positif sebagai wujud pengabdian masyarakat dalam bidang pendidikan. Edelweis terdiri dari tiga tahap utama: Pra-Edelweis, Edelweis, dan Pasca Edelweis, yang dilaksanakan di Kampus IPB serta di Desa Binaan IPB Mengajar.',
 'green', 'from-green-light via-green-medium to-green-dark', ''),

(2, 'DISPEN', 'Diskusi Pendidikan',
 'Dispen (Diskusi Pendidikan) merupakan mega proker yang dilaksanakan setelah kegiatan Edelweis. Dalam acara ini, berbagai isu penting terkait pendidikan, terutama yang berhubungan dengan Edelweis, akan dibahas secara mendalam. Dispen juga melibatkan berbagai stakeholder untuk mendapatkan solusi yang efektif demi kemajuan pendidikan Indonesia.',
 'blue', 'from-blue-light via-blue-deep to-green-dark', ''),

(3, 'IM-BLINKS', 'IM-Bimbingan Lingkar Kampus',
 'IM-Bimbingan Lingkar Kampus (IM-BLINKS) merupakan kegiatan bimbingan belajar yang akan berfokus untuk mengajar anak-anak yang putus sekolah di desa binaan yang ada di lingkar kampus dengan melibatkan seluruh manajemen IM, orang tua peserta bimbingan, serta pemerintah setempat. Kegiatan ini akan dilaksanakan secara periodik selama masa kepengurusan.',
 'yellow', 'from-yellow-secondary via-yellow-primary to-yellow-amber', ''),

(4, 'IM Internship', 'Pemberdayaan Pendidikan',
 'IM Internship merupakan bentuk serangkaian kegiatan untuk mengembangkan kemampuan dan pengetahuan bagi mahasiswa yang memiliki minat bidang pemberdayaan pendidikan di luar Manajemen IPB Mengajar yang diharapkan juga mampu mempromosikan UKM IPB Mengajar serta menunjang dan mendukung soft skill yang akan diimplementasikan selama ikut melaksanakan program IPB Mengajar dalam waktu 2 bulan.',
 'teal', 'from-green-medium via-green-light to-blue-light', ''),

(5, 'GOTA', 'Gerakan Orangtua Asuh',
 'GOTA (Gerakan Orangtua Asuh) merupakan salah satu program BPH IPB Mengajar yang bekerjasama dengan Agrianita IPB untuk memberikan pengajaran intensif dalam persiapan ujian sekolah baik tingkat SD, SMP, dan SMA sesuai kurikulum yang berlaku. Kegiatan ini akan dilaksanakan selama 20 pertemuan dalam 2 kali seminggu.',
 'amber', 'from-yellow-amber via-yellow-secondary to-brown-warm', '');
