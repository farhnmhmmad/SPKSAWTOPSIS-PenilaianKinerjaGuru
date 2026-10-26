SPK Evaluasi Kinerja Guru (SAW & TOPSIS)

Ringkasan
- Stack: PHP 8.3.36+ (gunakan rilis yang sudah menerima patch keamanan terbaru), MySQL/MariaDB, native (MVC ringan)
- Fitur awal: autentikasi login/logout (role), routing sederhana, koneksi DB (PDO), skema SQL.

Cara Menjalankan (Lokal)
1) Buat database MySQL, mis. `spk_evaluasi` lalu import `sql/schema.sql`.
2) Salin `.env.example` menjadi `.env`, isi kredensial DB.
3) Jalankan server: `php -S localhost:8000 -t public`.
4) Buka `http://localhost:8000`.

Deploy ke Vercel
- Vercel menjalankan PHP melalui runtime komunitas `vercel-php`; aplikasi membutuhkan database MySQL eksternal yang dapat diakses dari Vercel.
- TiDB Cloud adalah opsi MySQL-kompatibel di Vercel Marketplace. Hubungkan cluster ke proyek Vercel, lalu import `sql/schema.sql`; skema ini membuat tabel aplikasi dan tabel sesi serverless.
- Integrasi TiDB mengisi `TIDB_HOST`, `TIDB_PORT`, `TIDB_USER`, `TIDB_PASSWORD`, dan `TIDB_DATABASE`. Atur juga `SESSION_DRIVER=mysql` dan `APP_DEBUG=false`.
- Jika penyedia database mensyaratkan sertifikat TLS khusus, isi `DB_SSL_CA` dengan path sertifikat CA atau isi PEM sertifikatnya.
- `sql/schema.sql` tidak membuat akun default. Buat akun admin langsung di konsol database sebelum login pertama; jangan simpan password produksi di GitHub.
- Jangan impor dump `spk_evaluasikinerjaguru.sql` ke deployment publik tanpa meninjau dan mengizinkan publikasi data di dalamnya.

Keamanan PHP
- Gunakan PHP 8.3.36 atau lebih baru pada cabang 8.3. Jika memakai cabang PHP lain, pastikan sudah memasang rilis patch keamanannya.
- Rilis PHP 8.3.36 memperbaiki celah eksekusi kode jarak jauh PHP2Shell.

Akun Default
- `sql/schema.sql` tidak berisi akun default. Buat akun admin pertama melalui konsol database sebelum login pertama.

Struktur Folder
- `public/`     : front controller, aset.
- `app/`        : Core (router, auth), Controllers, Models, Views.
- `config/`     : env, database (PDO), helper bootstrap.
- `sql/`        : skema database.

Roadmap
1) CRUD Master (users, guru, kriteria, subkriteria, indikator, periode)
2) Input penilaian + detail indikator
3) Modul SAW & TOPSIS
4) Peringkat & Laporan

