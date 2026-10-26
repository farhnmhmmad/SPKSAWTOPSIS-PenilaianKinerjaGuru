<?php
declare(strict_types=1);

session_start();

// Autoload sangat sederhana
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) return;
    $relative = substr($class, strlen($prefix));
    $file = $base_dir . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) require $file;
});

// Use statements
use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\GuruController;
use App\Controllers\KriteriaController;
use App\Controllers\SubkriteriaController;
use App\Controllers\IndikatorController;
use App\Controllers\PenilaianController;
use App\Controllers\PerhitunganController;
use App\Controllers\PeringkatController;
use App\Controllers\ProfileController;
use App\Controllers\UserController;

// Require config
require __DIR__ . '/../config/env.php';
require __DIR__ . '/../config/database.php';

// Router
$router = new Router();

// Auth
$router->get('/', function () {
    if (isset($_SESSION['user'])) {
        $role = $_SESSION['user']['role'] ?? '';
        if ($role === 'guest') { header('Location: /peringkat'); exit; }
        if ($role === 'penilai') { header('Location: /penilaian'); exit; }
        header('Location: /dashboard'); exit;
    }
    header('Location: /login');
});
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/guest', [AuthController::class, 'guest']);
$router->get('/logout', [AuthController::class, 'logout']);

// Dashboard
$router->get('/dashboard', [DashboardController::class, 'index']);

// Guru
$router->get('/guru', [GuruController::class, 'index']);
$router->get('/guru/create', [GuruController::class, 'create']);
$router->post('/guru/store', [GuruController::class, 'store']);
$router->get('/guru/edit', [GuruController::class, 'edit']);
$router->post('/guru/update', [GuruController::class, 'update']);
$router->post('/guru/delete', [GuruController::class, 'delete']);

// Kriteria
$router->get('/kriteria', [KriteriaController::class, 'index']);
$router->get('/kriteria/create', [KriteriaController::class, 'create']);
$router->post('/kriteria/store', [KriteriaController::class, 'store']);
$router->get('/kriteria/edit', [KriteriaController::class, 'edit']);
$router->post('/kriteria/update', [KriteriaController::class, 'update']);
$router->post('/kriteria/delete', [KriteriaController::class, 'delete']);

// Subkriteria
$router->get('/subkriteria', [SubkriteriaController::class, 'index']);
$router->get('/subkriteria/create', [SubkriteriaController::class, 'create']);
$router->post('/subkriteria/store', [SubkriteriaController::class, 'store']);
$router->get('/subkriteria/edit', [SubkriteriaController::class, 'edit']);
$router->post('/subkriteria/update', [SubkriteriaController::class, 'update']);
$router->post('/subkriteria/delete', [SubkriteriaController::class, 'delete']);

// Indikator
$router->get('/indikator', [IndikatorController::class, 'index']);
$router->get('/indikator/create', [IndikatorController::class, 'create']);
$router->post('/indikator/store', [IndikatorController::class, 'store']);
$router->get('/indikator/edit', [IndikatorController::class, 'edit']);
$router->post('/indikator/update', [IndikatorController::class, 'update']);
$router->post('/indikator/delete', [IndikatorController::class, 'delete']);

// Penilaian Guru
$router->get('/penilaian', [PenilaianController::class, 'index']);
$router->get('/penilaian/create', [PenilaianController::class, 'create']);
$router->post('/penilaian/store', [PenilaianController::class, 'store']);
$router->get('/penilaian/edit', [PenilaianController::class, 'edit']);
$router->post('/penilaian/update', [PenilaianController::class, 'update']);
$router->post('/penilaian/delete', [PenilaianController::class, 'delete']);
$router->get('/penilaian/nilai', [PenilaianController::class, 'nilai']);
$router->post('/penilaian/simpan-nilai', [PenilaianController::class, 'simpanNilai']);
$router->post('/penilaian/hapus-nilai', [PenilaianController::class, 'hapusNilai']);
$router->get('/penilaian/cetak-detail', [PenilaianController::class, 'cetakDetail']);
$router->get('/penilaian/cetak-periode', [PenilaianController::class, 'cetakPeriode']);

// Perhitungan
$router->get('/perhitungan', [PerhitunganController::class, 'index']);
$router->post('/perhitungan/proses', [PerhitunganController::class, 'proses']);
$router->post('/perhitungan/status', [PerhitunganController::class, 'updateStatus']);
$router->get('/perhitungan/cetak', [PerhitunganController::class, 'cetak']);

// Perangkingan
$router->get('/peringkat', [PeringkatController::class, 'index']);
$router->get('/peringkat/cetak', [PeringkatController::class, 'cetak']);

// Profil
$router->get('/profile', [ProfileController::class, 'index']);
$router->post('/profile/update', [ProfileController::class, 'update']);

// Users
$router->get('/users', [UserController::class, 'index']);
$router->post('/users/store', [UserController::class, 'store']);
$router->post('/users/update', [UserController::class, 'update']);
$router->post('/users/delete', [UserController::class, 'delete']);

$router->dispatch();
