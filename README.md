SPK Evaluasi Kinerja Guru (SAW & TOPSIS)

Ringkasan
- Stack: PHP 8.3.36+ (gunakan rilis yang sudah menerima patch keamanan terbaru), MySQL/MariaDB, native (MVC ringan)
- Fitur awal: autentikasi login/logout (role), routing sederhana, koneksi DB (PDO), skema SQL.

Cara Menjalankan (Lokal)
1) Buat database MySQL, mis. `spk_evaluasi` lalu import `sql/schema.sql`.
2) Salin `.env.example` menjadi `.env`, isi kredensial DB.
3) Jalankan server: `php -S localhost:8000 -t public`.
4) Buka `http://localhost:8000`.

Keamanan PHP
- Gunakan PHP 8.3.36 atau lebih baru pada cabang 8.3. Jika memakai cabang PHP lain, pastikan sudah memasang rilis patch keamanannya.
- Rilis PHP 8.3.36 memperbaiki celah eksekusi kode jarak jauh PHP2Shell.

Akun Default
- Jika tabel `users` kosong, buat akun admin secara manual via SQL atau menu Users nanti.
- Contoh admin: username `admin`, password `admin123` (buat dengan `password_hash` jika via SQL).

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

