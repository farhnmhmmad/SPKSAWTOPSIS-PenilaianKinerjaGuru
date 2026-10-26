<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class SubkriteriaController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['admin']);
        $kriteria = $this->db->query('SELECT * FROM kriteria ORDER BY id_kriteria')->fetchAll();
        $subs = $this->db->query('SELECT * FROM subkriteria ORDER BY id_kriteria, id_subkriteria')->fetchAll();
        $grouped = [];
        foreach ($subs as $s) {
            $grouped[$s['id_kriteria']][] = $s;
        }
        $this->render('subkriteria/index', ['kriteria'=>$kriteria,'grouped'=>$grouped]);
    }

    public function create(): void
    {
        Auth::requireRole(['admin']);
        $kriteria = $this->db->query('SELECT * FROM kriteria ORDER BY id_kriteria')->fetchAll();
        $selected = (int)($_GET['id_kriteria'] ?? 0);
        $this->render('subkriteria/form', ['mode'=>'create','row'=>['id_kriteria'=>$selected],'kriteria'=>$kriteria]);
    }

    public function store(): void
    {
        Auth::requireRole(['admin']);
        $idKrit = (int)($_POST['id_kriteria'] ?? 0);
        $batch = $_POST['subkriteria'] ?? null;
        if (is_array($batch)) {
            $stmt = $this->db->prepare('INSERT INTO subkriteria (id_kriteria,nama_subkriteria) VALUES (:k,:nama)');
            foreach ($batch as $row) {
                $nama = trim($row['nama_subkriteria'] ?? '');
                if ($nama === '') continue;
                $stmt->execute([':k'=>$idKrit, ':nama'=>$nama]);
            }
        } else {
            $stmt = $this->db->prepare('INSERT INTO subkriteria (id_kriteria,nama_subkriteria) VALUES (:k,:nama)');
            $stmt->execute([
                ':k'=>$idKrit,
                ':nama'=>trim($_POST['nama_subkriteria'] ?? ''),
            ]);
        }
        $this->flash('Subkriteria berhasil ditambahkan');
        $this->redirect('/subkriteria');
    }

    public function edit(): void
    {
        Auth::requireRole(['admin']);
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $this->db->prepare('SELECT * FROM subkriteria WHERE id_subkriteria=:id');
        $stmt->execute([':id'=>$id]);
        $row = $stmt->fetch();
        if (!$row) $this->redirect('/subkriteria');
        $kriteria = $this->db->query('SELECT * FROM kriteria ORDER BY id_kriteria')->fetchAll();
        $this->render('subkriteria/form', ['mode'=>'edit','row'=>$row,'kriteria'=>$kriteria]);
    }

    public function update(): void
    {
        Auth::requireRole(['admin']);
        $stmt = $this->db->prepare('UPDATE subkriteria SET id_kriteria=:k,nama_subkriteria=:nama WHERE id_subkriteria=:id');
        $stmt->execute([
            ':id'=>(int)($_POST['id'] ?? 0),
            ':k'=>(int)($_POST['id_kriteria'] ?? 0),
            ':nama'=>trim($_POST['nama_subkriteria'] ?? ''),
        ]);
        $this->flash('Subkriteria berhasil diubah');
        $this->redirect('/subkriteria');
    }

    public function delete(): void
    {
        Auth::requireRole(['admin']);
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $this->db->prepare('DELETE FROM subkriteria WHERE id_subkriteria=:id');
        $stmt->execute([':id'=>$id]);
        $this->flash('Subkriteria berhasil dihapus');
        $this->redirect('/subkriteria');
    }
}
