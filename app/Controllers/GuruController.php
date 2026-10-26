<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class GuruController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['admin']);
        $rows = $this->db->query('SELECT * FROM guru ORDER BY nama_guru')->fetchAll(\PDO::FETCH_ASSOC);
        $this->render('guru/index', ['data' => $rows]);
    }

    public function create(): void
    {
        Auth::requireRole(['admin']);
        $this->render('guru/form', ['mode' => 'create', 'row' => []]);
    }

    public function store(): void
    {
        Auth::requireRole(['admin']);
        $nama = trim($_POST['nama_guru'] ?? '');
        $jk = $_POST['jenis_kelamin'] ?? '';
        $nip = trim($_POST['nip_guru'] ?? '');
        if ($nama === '' || $nip === '') { $this->back('/guru'); }
        $stmt = $this->db->prepare('INSERT INTO guru (nama_guru, nip_guru, jenis_kelamin) VALUES (:nama,:nip,:jk)');
        $stmt->execute([
            ':nama' => $nama,
            ':nip' => $nip,
            ':jk'  => $jk,
        ]);
        $this->flash('Guru berhasil ditambahkan');
        $this->redirect('/guru');
    }

    public function edit(): void
    {
        Auth::requireRole(['admin']);
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $this->db->prepare('SELECT * FROM guru WHERE id_guru=:id');
        $stmt->execute([':id'=>$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) { $this->redirect('/guru'); }
        $this->render('guru/form', ['mode' => 'edit', 'row' => $row]);
    }

    public function update(): void
    {
        Auth::requireRole(['admin']);
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $this->db->prepare('UPDATE guru SET nama_guru=:nama,nip_guru=:nip,jenis_kelamin=:jk WHERE id_guru=:id');
        $stmt->execute([
            ':id'=>$id,
            ':nama'=>trim($_POST['nama_guru'] ?? ''),
            ':nip'=>$_POST['nip_guru'] ?? null,
            ':jk'=>$_POST['jenis_kelamin'] ?? '',
        ]);
        $this->flash('Guru berhasil diubah');
        $this->redirect('/guru');
    }

    public function delete(): void
    {
        Auth::requireRole(['admin']);
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $this->db->prepare('DELETE FROM guru WHERE id_guru=:id');
        $stmt->execute([':id'=>$id]);
        $this->flash('Guru berhasil dihapus');
        $this->redirect('/guru');
    }
}
