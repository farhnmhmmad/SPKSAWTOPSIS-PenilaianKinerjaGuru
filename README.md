SPK Evaluasi Kinerja Guru (SAW & TOPSIS)

Ringkasan
- Stack: PHP 8+, MySQL/MariaDB, native (MVC ringan)
- Fitur awal: autentikasi login/logout (role), routing sederhana, koneksi DB (PDO), skema SQL.

Cara Menjalankan (Lokal)
1) Buat database MySQL, mis. `spk_evaluasi` lalu import `sql/schema.sql`.
2) Salin `.env.example` menjadi `.env`, isi kredensial DB.
3) Jalankan server: `php -S localhost:8000 -t public`.
4) Buka `http://localhost:8000`.

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

