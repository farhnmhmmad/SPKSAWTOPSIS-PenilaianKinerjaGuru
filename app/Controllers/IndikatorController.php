<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class IndikatorController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['admin']);
        $kriteria = $this->db->query('SELECT * FROM kriteria ORDER BY id_kriteria')->fetchAll();
        $subs = $this->db->query('SELECT * FROM subkriteria ORDER BY id_kriteria, id_subkriteria')->fetchAll();
        $indikator = $this->db->query('SELECT * FROM indikator ORDER BY id_subkriteria, id_indikator')->fetchAll();
        $subGrouped = [];
        foreach ($subs as $s) {
            $subGrouped[$s['id_kriteria']][] = $s;
        }
        $indiGrouped = [];
        foreach ($indikator as $i) {
            $indiGrouped[$i['id_subkriteria']][] = $i;
        }
        $this->render('indikator/index', [
            'kriteria'=>$kriteria,
            'subGrouped'=>$subGrouped,
            'indiGrouped'=>$indiGrouped,
        ]);
    }

    public function create(): void
    {
        Auth::requireRole(['admin']);
        $selectedSub = (int)($_GET['id_subkriteria'] ?? 0);
        $sub = $this->db->query('SELECT s.id_subkriteria, s.nama_subkriteria, k.nama_kriteria FROM subkriteria s JOIN kriteria k ON k.id_kriteria = s.id_kriteria ORDER BY k.id_kriteria, s.id_subkriteria')->fetchAll();
        $this->render('indikator/form', ['mode'=>'create','row'=>['id_subkriteria'=>$selectedSub],'sub'=>$sub]);
    }

    public function store(): void
    {
        Auth::requireRole(['admin']);
        $idSub = (int)($_POST['id_subkriteria'] ?? 0);
        $batch = $_POST['indikator'] ?? null;
        if (is_array($batch)) {
            $stmt = $this->db->prepare('INSERT INTO indikator (id_subkriteria, nama_indikator) VALUES (:s,:nama)');
            foreach ($batch as $row) {
                $stmt->execute([
                    ':s'=>$idSub,
                    ':nama'=>trim($row['teks'] ?? ''),
                ]);
            }
        } else {
            $stmt = $this->db->prepare('INSERT INTO indikator (id_subkriteria, nama_indikator) VALUES (:s,:nama)');
            $stmt->execute([
                ':s'=>$idSub,
                ':nama'=>trim($_POST['teks'] ?? ''),
            ]);
        }
        $this->flash('Indikator berhasil ditambahkan');
        $this->redirect('/indikator');
    }

    public function edit(): void
    {
        Auth::requireRole(['admin']);
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $this->db->prepare('SELECT * FROM indikator WHERE id_indikator=:id');
        $stmt->execute([':id'=>$id]);
        $row = $stmt->fetch();
        if (!$row) $this->redirect('/indikator');
        $sub = $this->db->query('SELECT s.id_subkriteria, s.nama_subkriteria, k.nama_kriteria FROM subkriteria s JOIN kriteria k ON k.id_kriteria = s.id_kriteria ORDER BY k.id_kriteria, s.id_subkriteria')->fetchAll();
        $this->render('indikator/form', ['mode'=>'edit','row'=>$row,'sub'=>$sub]);
    }

    public function update(): void
    {
        Auth::requireRole(['admin']);
        $stmt = $this->db->prepare('UPDATE indikator SET id_subkriteria=:s, nama_indikator=:nama WHERE id_indikator=:id');
        $stmt->execute([
            ':id'=>(int)($_POST['id'] ?? 0),
            ':s'=>(int)($_POST['id_subkriteria'] ?? 0),
            ':nama'=>trim($_POST['teks'] ?? ''),
        ]);
        $this->flash('Indikator berhasil diubah');
        $this->redirect('/indikator');
    }

    public function delete(): void
    {
        Auth::requireRole(['admin']);
        $id = (int)($_POST['id'] ?? 0);
        $this->db->prepare('DELETE FROM indikator WHERE id_indikator=:id')->execute([':id'=>$id]);
        $this->flash('Indikator berhasil dihapus');
        $this->redirect('/indikator');
    }
}
