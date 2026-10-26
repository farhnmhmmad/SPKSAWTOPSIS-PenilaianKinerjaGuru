<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class PenilaianController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['admin','penilai']);
        $rows = $this->db->query("SELECT * FROM penilaian ORDER BY tanggal_penilaian DESC")->fetchAll();
        $lockedMap = [];
        foreach ($rows as $r) {
            $lockedMap[$r['id_penilaian']] = $this->isLocked((int)$r['id_penilaian']);
        }
        $this->render('penilaian/index', ['data'=>$rows, 'lockedMap'=>$lockedMap]);
    }

    public function create(): void
    {
        Auth::requireRole(['admin','penilai']);
        $this->render('penilaian/form', ['mode'=>'create','row'=>[]]);
    }

    public function store(): void
    {
        Auth::requireRole(['admin','penilai']);
        $nama = trim($_POST['nama_penilai'] ?? '');
        $nip = trim($_POST['nip_penilai'] ?? '');
        $periode = trim($_POST['periode'] ?? '');
        $userId = (int)($_SESSION['user']['id_users'] ?? 0);
        $stmt = $this->db->prepare('INSERT INTO penilaian (id_users, nama_penilai, nip_penilai, periode) VALUES (:uid, :nama, :nip, :periode)');
        $stmt->execute([':uid'=>$userId, ':nama'=>$nama, ':nip'=>$nip, ':periode'=>$periode]);
        $this->flash('Penilaian berhasil ditambahkan');
        $this->redirect('/penilaian');
    }

    public function edit(): void
    {
        Auth::requireRole(['admin','penilai']);
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $this->db->prepare('SELECT * FROM penilaian WHERE id_penilaian=:id');
        $stmt->execute([':id'=>$id]);
        $row = $stmt->fetch();
        if (!$row) $this->redirect('/penilaian');
        $this->render('penilaian/form', ['mode'=>'edit','row'=>$row]);
    }

    public function update(): void
    {
        Auth::requireRole(['admin','penilai']);
        $userId = (int)($_SESSION['user']['id_users'] ?? 0);
        $stmt = $this->db->prepare('UPDATE penilaian SET id_users=:uid, nama_penilai=:nama, nip_penilai=:nip, periode=:periode WHERE id_penilaian=:id');
        $stmt->execute([
            ':id'=>(int)($_POST['id'] ?? 0),
            ':uid'=>$userId,
            ':nama'=>trim($_POST['nama_penilai'] ?? ''),
            ':nip'=>trim($_POST['nip_penilai'] ?? ''),
            ':periode'=>trim($_POST['periode'] ?? ''),
        ]);
        $this->flash('Penilaian berhasil diubah');
        $this->redirect('/penilaian');
    }

    public function delete(): void
    {
        Auth::requireRole(['admin','penilai']);
        $id = (int)($_POST['id'] ?? 0);
        if ($this->isLocked($id)) {
            $this->redirect('/penilaian?err=locked');
        }
        $this->db->prepare('DELETE FROM perhitungan_saw WHERE id_penilaian=:id')->execute([':id'=>$id]);
        $this->db->prepare('DELETE FROM perhitungan_topsis WHERE id_penilaian=:id')->execute([':id'=>$id]);
        $this->db->prepare('DELETE FROM penilaian WHERE id_penilaian=:id')->execute([':id'=>$id]);
        $this->flash('Penilaian berhasil dihapus');
        $this->redirect('/penilaian');
    }

    public function nilai(): void
    {
        Auth::requireRole(['admin','penilai']);
        $id = (int)($_GET['id'] ?? 0);
        $idGuru = (int)($_GET['id_guru'] ?? 0);
        $stmt = $this->db->prepare('SELECT * FROM penilaian WHERE id_penilaian=:id');
        $stmt->execute([':id'=>$id]);
        $penilaian = $stmt->fetch();
        if (!$penilaian) { $this->redirect('/penilaian'); }

        $guru = $this->db->query('SELECT * FROM guru ORDER BY nama_guru')->fetchAll();
        $kriteria = $this->db->query('SELECT * FROM kriteria ORDER BY id_kriteria')->fetchAll();
        $sub = $this->db->query('SELECT s.*, k.id_kriteria FROM subkriteria s JOIN kriteria k ON k.id_kriteria = s.id_kriteria ORDER BY k.id_kriteria, s.id_subkriteria')->fetchAll();
        $kriteria = $this->db->query('SELECT * FROM kriteria ORDER BY id_kriteria')->fetchAll();
        $indikator = $this->db->query('SELECT * FROM indikator ORDER BY id_subkriteria, id_indikator')->fetchAll();
        $subToKriteria = [];
        foreach ($sub as $sRow) {
            $subToKriteria[(int)$sRow['id_subkriteria']] = (int)$sRow['id_kriteria'];
        }
        $locked = $this->isLocked($id);
        $expectedIndikator = count($indikator);
        $allDetailRows = $this->db->prepare('SELECT id_guru, id_indikator, nilai FROM penilaian_detail WHERE id_penilaian=:p');
        $allDetailRows->execute([':p'=>$id]);
        $allDetail = [];
        foreach ($allDetailRows->fetchAll() as $dRow) {
            $g = (int)$dRow['id_guru'];
            $i = (int)$dRow['id_indikator'];
            $allDetail[$g][$i] = (int)$dRow['nilai'];
        }
        $allCatatanKriteria = [];
        $allCatatanRows = $this->db->prepare('SELECT id_guru, id_subkriteria, catatan FROM penilaian_catatan WHERE id_penilaian=:p');
        $allCatatanRows->execute([':p'=>$id]);
        foreach ($allCatatanRows->fetchAll() as $cRow) {
            $g = (int)$cRow['id_guru'];
            $s = (int)$cRow['id_subkriteria'];
            $k = $subToKriteria[$s] ?? 0;
            if ($k <= 0) continue;
            $text = trim((string)($cRow['catatan'] ?? ''));
            if ($text === '') continue;
            if (!isset($allCatatanKriteria[$g][$k])) {
                $allCatatanKriteria[$g][$k] = $text;
            }
        }
        $nilaiMap = [];
        $detailAggStmt = $this->db->prepare('SELECT id_guru, COUNT(*) AS total FROM penilaian_detail WHERE id_penilaian=:p GROUP BY id_guru');
        $detailAggStmt->execute([':p'=>$id]);
        foreach ($detailAggStmt->fetchAll() as $rowAgg) {
            $nilaiMap[(int)$rowAgg['id_guru']] = (int)$rowAgg['total'];
        }
        $guruSelesai = 0;
        $guruPartial = 0;
        foreach ($nilaiMap as $cnt) {
            if ($expectedIndikator > 0 && $cnt >= $expectedIndikator) {
                $guruSelesai++;
            } elseif ($cnt > 0) {
                $guruPartial++;
            }
        }
        $summary = [
            'guru_total' => count($guru),
            'guru_selesai' => $guruSelesai,
            'guru_partial' => $guruPartial,
            'kriteria_total' => count($kriteria),
            'sub_total' => count($sub),
            'indikator_total' => $expectedIndikator,
            'nilai_map' => $nilaiMap,
        ];

        $detail = [];
        $catatanKriteriaMap = [];
        if ($idGuru) {
            $detailStmt = $this->db->prepare('SELECT * FROM penilaian_detail WHERE id_penilaian=:p AND id_guru=:g');
            $detailStmt->execute([':p'=>$id, ':g'=>$idGuru]);
            foreach ($detailStmt->fetchAll() as $d) {
                $detail[$d['id_indikator']] = (int)$d['nilai'];
            }
            $catStmt = $this->db->prepare('SELECT id_subkriteria, catatan FROM penilaian_catatan WHERE id_penilaian=:p AND id_guru=:g');
            $catStmt->execute([':p'=>$id, ':g'=>$idGuru]);
            foreach ($catStmt->fetchAll() as $c) {
                $subId = (int)$c['id_subkriteria'];
                $k = $subToKriteria[$subId] ?? 0;
                if ($k <= 0) continue;
                $text = trim((string)($c['catatan'] ?? ''));
                if ($text === '') continue;
                if (!isset($catatanKriteriaMap[$k])) {
                    $catatanKriteriaMap[$k] = $text;
                }
            }
        }

        $this->render('penilaian/nilai', [
            'penilaian'=>$penilaian,
            'guru'=>$guru,
            'kriteria'=>$kriteria,
            'sub'=>$sub,
            'indikator'=>$indikator,
            'detail'=>$detail,
            'catatanKriteriaMap'=>$catatanKriteriaMap,
            'selectedGuru'=>$idGuru,
            'locked'=>$locked,
            'allDetail'=>$allDetail,
            'allCatatanKriteria'=>$allCatatanKriteria,
            'summary'=>$summary,
        ]);
    }

    public function simpanNilai(): void
    {
        Auth::requireRole(['admin','penilai']);
        $idPenilaian = (int)($_POST['id_penilaian'] ?? 0);
        $idGuru = (int)($_POST['id_guru'] ?? 0);
        $nilai = $_POST['nilai'] ?? [];
        $catatanKriteria = $_POST['catatan_kriteria'] ?? [];
        if (!$idPenilaian || !$idGuru) { $this->redirect('/penilaian'); }
        if ($this->isLocked($idPenilaian)) {
            $this->redirect('/penilaian/nilai?id='.$idPenilaian.'&id_guru='.$idGuru.'&err=locked');
        }
        $subRows = $this->db->query('SELECT id_subkriteria, id_kriteria FROM subkriteria ORDER BY id_kriteria, id_subkriteria')->fetchAll();
        $firstSubByKriteria = [];
        foreach ($subRows as $s) {
            $sid = (int)$s['id_subkriteria'];
            $kid = (int)$s['id_kriteria'];
            if ($kid <= 0 || isset($firstSubByKriteria[$kid])) continue;
            $firstSubByKriteria[$kid] = $sid;
        }
        foreach ($firstSubByKriteria as $kid => $sid) {
            $text = trim((string)($catatanKriteria[$kid] ?? ''));
            if ($text === '') {
                $this->flash('Catatan bukti penilaian wajib diisi untuk semua kriteria utama.', 'error');
                $this->redirect('/penilaian/nilai?id='.$idPenilaian.'&id_guru='.$idGuru);
            }
        }
        $this->db->beginTransaction();
        $this->db->prepare('DELETE FROM penilaian_detail WHERE id_penilaian=:p AND id_guru=:g')->execute([':p'=>$idPenilaian, ':g'=>$idGuru]);
        $this->db->prepare('DELETE FROM penilaian_catatan WHERE id_penilaian=:p AND id_guru=:g')->execute([':p'=>$idPenilaian, ':g'=>$idGuru]);
        $this->db->prepare('DELETE FROM perhitungan_saw WHERE id_penilaian=:id')->execute([':id'=>$idPenilaian]);
        $this->db->prepare('DELETE FROM perhitungan_topsis WHERE id_penilaian=:id')->execute([':id'=>$idPenilaian]);
        $insertDetail = $this->db->prepare('INSERT INTO penilaian_detail (id_penilaian,id_guru,id_indikator,nilai) VALUES (:p,:g,:i,:n)');
        foreach ($nilai as $indiId => $val) {
            $v = (int)$val;
            $insertDetail->execute([
                ':p'=>$idPenilaian,
                ':g'=>$idGuru,
                ':i'=>(int)$indiId,
                ':n'=>$v,
            ]);
        }
        $insertCatatan = $this->db->prepare('INSERT INTO penilaian_catatan (id_penilaian,id_guru,id_subkriteria,catatan) VALUES (:p,:g,:s,:c)');
        foreach ($firstSubByKriteria as $kid => $subId) {
            $t = trim((string)($catatanKriteria[$kid] ?? ''));
            if ($t === '') continue;
            $insertCatatan->execute([
                ':p'=>$idPenilaian,
                ':g'=>$idGuru,
                ':s'=>(int)$subId,
                ':c'=>$t,
            ]);
        }

        $this->db->commit();
        $this->flash('Nilai berhasil disimpan');
        $this->redirect('/penilaian/nilai?id=' . $idPenilaian);
    }

    public function hapusNilai(): void
    {
        Auth::requireRole(['admin','penilai']);
        $idPenilaian = (int)($_POST['id_penilaian'] ?? 0);
        $idGuru = (int)($_POST['id_guru'] ?? 0);
        if (!$idPenilaian || !$idGuru) { $this->redirect('/penilaian'); }
        if ($this->isLocked($idPenilaian)) {
            $this->redirect('/penilaian/nilai?id='.$idPenilaian.'&err=locked');
        }
        $this->db->beginTransaction();
        $this->db->prepare('DELETE FROM penilaian_detail WHERE id_penilaian=:p AND id_guru=:g')->execute([':p'=>$idPenilaian, ':g'=>$idGuru]);
        $this->db->prepare('DELETE FROM perhitungan_saw WHERE id_penilaian=:id')->execute([':id'=>$idPenilaian]);
        $this->db->prepare('DELETE FROM perhitungan_topsis WHERE id_penilaian=:id')->execute([':id'=>$idPenilaian]);
        $this->db->commit();
        $this->flash('Nilai berhasil dihapus');
        $this->redirect('/penilaian/nilai?id=' . $idPenilaian);
    }

    public function cetakDetail(): void
    {
        Auth::requireRole(['admin','penilai']);
        $id = (int)($_GET['id'] ?? 0);
        $idGuru = (int)($_GET['id_guru'] ?? 0);
        if (!$id || !$idGuru) { $this->redirect('/penilaian'); }

        $stmt = $this->db->prepare('SELECT * FROM penilaian WHERE id_penilaian=:id');
        $stmt->execute([':id'=>$id]);
        $penilaian = $stmt->fetch();
        if (!$penilaian) { $this->redirect('/penilaian'); }

        $guruStmt = $this->db->prepare('SELECT * FROM guru WHERE id_guru=:g');
        $guruStmt->execute([':g'=>$idGuru]);
        $guru = $guruStmt->fetch();
        if (!$guru) { $this->redirect('/penilaian/nilai?id='.$id); }

        $sub = $this->db->query('SELECT s.*, k.id_kriteria FROM subkriteria s JOIN kriteria k ON k.id_kriteria = s.id_kriteria ORDER BY k.id_kriteria, s.id_subkriteria')->fetchAll();
        $kriteria = $this->db->query('SELECT * FROM kriteria ORDER BY id_kriteria')->fetchAll();
        $indikator = $this->db->query('SELECT * FROM indikator ORDER BY id_subkriteria, id_indikator')->fetchAll();

        $detailStmt = $this->db->prepare('SELECT id_indikator, nilai FROM penilaian_detail WHERE id_penilaian=:p AND id_guru=:g');
        $detailStmt->execute([':p'=>$id, ':g'=>$idGuru]);
        $detail = [];
        foreach ($detailStmt->fetchAll() as $d) {
            $detail[(int)$d['id_indikator']] = (int)$d['nilai'];
        }

        $catStmt = $this->db->prepare('SELECT id_subkriteria, catatan FROM penilaian_catatan WHERE id_penilaian=:p AND id_guru=:g');
        $catStmt->execute([':p'=>$id, ':g'=>$idGuru]);
        $subToKriteria = [];
        foreach ($sub as $sRow) {
            $subToKriteria[(int)$sRow['id_subkriteria']] = (int)$sRow['id_kriteria'];
        }
        $catatanByKriteria = [];
        foreach ($catStmt->fetchAll() as $c) {
            $subId = (int)$c['id_subkriteria'];
            $k = $subToKriteria[$subId] ?? 0;
            if ($k <= 0 || isset($catatanByKriteria[$k])) continue;
            $catatanByKriteria[$k] = $c['catatan'] ?? '';
        }
        $catatanMap = [];
        foreach ($sub as $sRow) {
            $subId = (int)$sRow['id_subkriteria'];
            $k = (int)$sRow['id_kriteria'];
            $catatanMap[$subId] = $catatanByKriteria[$k] ?? '';
        }

        $this->render('penilaian/cetak_detail', [
            'penilaian'=>$penilaian,
            'guru'=>$guru,
            'kriteria'=>$kriteria,
            'sub'=>$sub,
            'indikator'=>$indikator,
            'detail'=>$detail,
            'catatanMap'=>$catatanMap,
        ]);
    }

    public function cetakPeriode(): void
    {
        Auth::requireRole(['admin','penilai']);
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { $this->redirect('/penilaian'); }

        $stmt = $this->db->prepare('SELECT * FROM penilaian WHERE id_penilaian=:id');
        $stmt->execute([':id'=>$id]);
        $penilaian = $stmt->fetch();
        if (!$penilaian) { $this->redirect('/penilaian'); }

        $guru = $this->db->query('SELECT * FROM guru ORDER BY nama_guru')->fetchAll();
        $sub = $this->db->query('SELECT s.*, k.id_kriteria FROM subkriteria s JOIN kriteria k ON k.id_kriteria = s.id_kriteria ORDER BY k.id_kriteria, s.id_subkriteria')->fetchAll();
        $kriteria = $this->db->query('SELECT * FROM kriteria ORDER BY id_kriteria')->fetchAll();
        $indikator = $this->db->query('SELECT * FROM indikator ORDER BY id_subkriteria, id_indikator')->fetchAll();

        $detailRows = $this->db->prepare('SELECT id_guru, id_indikator, nilai FROM penilaian_detail WHERE id_penilaian=:p');
        $detailRows->execute([':p'=>$id]);
        $detailMap = [];
        foreach ($detailRows->fetchAll() as $d) {
            $detailMap[(int)$d['id_guru']][(int)$d['id_indikator']] = (int)$d['nilai'];
        }

        $catRows = $this->db->prepare('SELECT id_guru, id_subkriteria, catatan FROM penilaian_catatan WHERE id_penilaian=:p');
        $catRows->execute([':p'=>$id]);
        $subToKriteria = [];
        foreach ($sub as $sRow) {
            $subToKriteria[(int)$sRow['id_subkriteria']] = (int)$sRow['id_kriteria'];
        }
        $catatanByKriteria = [];
        foreach ($catRows->fetchAll() as $c) {
            $gid = (int)$c['id_guru'];
            $subId = (int)$c['id_subkriteria'];
            $k = $subToKriteria[$subId] ?? 0;
            if ($k <= 0 || isset($catatanByKriteria[$gid][$k])) continue;
            $catatanByKriteria[$gid][$k] = $c['catatan'] ?? '';
        }
        $catatanMap = [];
        foreach ($guru as $gRow) {
            $gid = (int)$gRow['id_guru'];
            foreach ($sub as $sRow) {
                $subId = (int)$sRow['id_subkriteria'];
                $k = (int)$sRow['id_kriteria'];
                $catatanMap[$gid][$subId] = $catatanByKriteria[$gid][$k] ?? '';
            }
        }

        $this->render('penilaian/cetak_periode', [
            'penilaian'=>$penilaian,
            'guru'=>$guru,
            'kriteria'=>$kriteria,
            'sub'=>$sub,
            'indikator'=>$indikator,
            'detailMap'=>$detailMap,
            'catatanMap'=>$catatanMap,
        ]);
    }

    private function isLocked(int $idPenilaian): bool
    {
        if (!$idPenilaian) return false;
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM perhitungan_saw WHERE id_penilaian=:id AND status="published"');
        $stmt->execute([':id'=>$idPenilaian]);
        $saw = (int)$stmt->fetchColumn();
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM perhitungan_topsis WHERE id_penilaian=:id AND status="published"');
        $stmt->execute([':id'=>$idPenilaian]);
        $top = (int)$stmt->fetchColumn();
        return ($saw + $top) > 0;
    }
}
