<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class KriteriaController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['admin']);
        $rows = $this->db->query('SELECT * FROM kriteria ORDER BY id_kriteria')->fetchAll();
        $this->render('kriteria/index', ['data'=>$rows]);
    }

    public function create(): void
    {
        Auth::requireRole(['admin']);
        $this->render('kriteria/form', ['mode'=>'create','row'=>[]]);
    }

    public function store(): void
    {
        Auth::requireRole(['admin']);
        $stmt = $this->db->prepare('INSERT INTO kriteria (kode_kriteria,nama_kriteria,bobot_kriteria,tipe_kriteria) VALUES (:kode,:nama,:bobot,:tipe)');
        $stmt->execute([
            ':kode'=>trim($_POST['kode'] ?? ''),
            ':nama'=>trim($_POST['nama'] ?? ''),
            ':bobot'=>(float)($_POST['bobot'] ?? 0),
            ':tipe'=>$_POST['tipe'] === 'cost' ? 'cost' : 'benefit',
        ]);
        $this->flash('Kriteria berhasil ditambahkan');
        $this->redirect('/kriteria');
    }

    public function edit(): void
    {
        Auth::requireRole(['admin']);
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $this->db->prepare('SELECT * FROM kriteria WHERE id_kriteria=:id');
        $stmt->execute([':id'=>$id]);
        $row = $stmt->fetch();
        if (!$row) $this->redirect('/kriteria');
        $this->render('kriteria/form', ['mode'=>'edit','row'=>$row]);
    }

    public function update(): void
    {
        Auth::requireRole(['admin']);
        $stmt = $this->db->prepare('UPDATE kriteria SET kode_kriteria=:kode,nama_kriteria=:nama,bobot_kriteria=:bobot,tipe_kriteria=:tipe WHERE id_kriteria=:id');
        $stmt->execute([
            ':id'=>(int)($_POST['id'] ?? 0),
            ':kode'=>trim($_POST['kode'] ?? ''),
            ':nama'=>trim($_POST['nama'] ?? ''),
            ':bobot'=>(float)($_POST['bobot'] ?? 0),
            ':tipe'=>$_POST['tipe'] === 'cost' ? 'cost' : 'benefit',
        ]);
        $this->flash('Kriteria berhasil diubah');
        $this->redirect('/kriteria');
    }

    public function delete(): void
    {
        Auth::requireRole(['admin']);
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $this->db->prepare('DELETE FROM kriteria WHERE id_kriteria=:id');
        $stmt->execute([':id'=>$id]);
        $this->flash('Kriteria berhasil dihapus');
        $this->redirect('/kriteria');
    }
}
