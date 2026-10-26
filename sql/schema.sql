-- Schema SPK Evaluasi Kinerja Guru (versi baru)

-- 1) users
CREATE TABLE users (
  id_users INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(100),
    role ENUM('admin','penilai') NOT NULL DEFAULT 'penilai',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 1b) Persistent PHP sessions for serverless deployments
CREATE TABLE app_sessions (
  session_id VARCHAR(128) NOT NULL PRIMARY KEY,
  session_data MEDIUMBLOB NOT NULL,
  expires_at DATETIME NOT NULL,
  KEY ix_app_sessions_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2) guru
CREATE TABLE guru (
  id_guru INT AUTO_INCREMENT PRIMARY KEY,
  nama_guru VARCHAR(150) NOT NULL,
  nip_guru VARCHAR(18),
  jenis_kelamin ENUM('L','P') NOT NULL DEFAULT 'L',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3) kriteria
CREATE TABLE kriteria (
  id_kriteria INT AUTO_INCREMENT PRIMARY KEY,
  kode_kriteria VARCHAR(20),
  nama_kriteria VARCHAR(100) NOT NULL,
  bobot_kriteria DECIMAL(6,4) NOT NULL DEFAULT 0.0000,
  tipe_kriteria ENUM('benefit','cost') NOT NULL DEFAULT 'benefit'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4) subkriteria
CREATE TABLE subkriteria (
  id_subkriteria INT AUTO_INCREMENT PRIMARY KEY,
  id_kriteria INT NOT NULL,
  nama_subkriteria VARCHAR(400) NOT NULL,
  FOREIGN KEY (id_kriteria) REFERENCES kriteria(id_kriteria) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5) indikator
CREATE TABLE indikator (
  id_indikator INT AUTO_INCREMENT PRIMARY KEY,
  id_subkriteria INT NOT NULL,
  nama_indikator VARCHAR(400) NOT NULL,
  FOREIGN KEY (id_subkriteria) REFERENCES subkriteria(id_subkriteria) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6) penilaian
CREATE TABLE penilaian (
  id_penilaian INT AUTO_INCREMENT PRIMARY KEY,
  id_users INT NOT NULL,
  periode VARCHAR(100) NOT NULL,
  nama_penilai VARCHAR(150) NOT NULL,
  nip_penilai VARCHAR(18),
  tanggal_penilaian TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_users) REFERENCES users(id_users) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7) penilaian_detail
CREATE TABLE penilaian_detail (
  id_penilaian_detail INT AUTO_INCREMENT PRIMARY KEY,
  id_penilaian INT NOT NULL,
  id_guru INT NOT NULL,
  id_indikator INT NOT NULL,
  nilai TINYINT NOT NULL CHECK (nilai BETWEEN 0 AND 2),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_penilaian) REFERENCES penilaian(id_penilaian) ON DELETE CASCADE,
  FOREIGN KEY (id_guru) REFERENCES guru(id_guru) ON DELETE CASCADE,
  FOREIGN KEY (id_indikator) REFERENCES indikator(id_indikator) ON DELETE CASCADE,
  UNIQUE KEY ux_pen_guru_ind (id_penilaian, id_guru, id_indikator)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7b) penilaian_catatan
CREATE TABLE penilaian_catatan (
  id_penilaian_catatan INT AUTO_INCREMENT PRIMARY KEY,
  id_penilaian INT NOT NULL,
  id_guru INT NOT NULL,
  id_subkriteria INT NOT NULL,
  catatan TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY ux_pen_guru_sub (id_penilaian, id_guru, id_subkriteria),
  FOREIGN KEY (id_penilaian) REFERENCES penilaian(id_penilaian) ON DELETE CASCADE,
  FOREIGN KEY (id_guru) REFERENCES guru(id_guru) ON DELETE CASCADE,
  FOREIGN KEY (id_subkriteria) REFERENCES subkriteria(id_subkriteria) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8) perhitungan_saw
CREATE TABLE perhitungan_saw (
  id_perhitungan_saw INT AUTO_INCREMENT PRIMARY KEY,
  id_penilaian INT NOT NULL,
  id_guru INT NOT NULL,
  skor_saw DOUBLE NOT NULL,
  peringkat_saw INT,
  status ENUM('published','archived') NOT NULL DEFAULT 'published',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY ux_hasil_saw_pen_guru (id_penilaian, id_guru),
  FOREIGN KEY (id_penilaian) REFERENCES penilaian(id_penilaian)
      ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (id_guru) REFERENCES guru(id_guru)
      ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9) perhitungan_topsis
CREATE TABLE perhitungan_topsis (
  id_perhitungan_topsis INT AUTO_INCREMENT PRIMARY KEY,
  id_penilaian INT NOT NULL,
  id_guru INT NOT NULL,
  skor_topsis DOUBLE NOT NULL,
  jarak_positif DOUBLE,
  jarak_negatif DOUBLE,
  peringkat_topsis INT,
  status ENUM('published','archived') NOT NULL DEFAULT 'published',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY ux_hasil_top_pen_guru (id_penilaian, id_guru),
  FOREIGN KEY (id_penilaian) REFERENCES penilaian(id_penilaian)
      ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (id_guru) REFERENCES guru(id_guru)
      ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
