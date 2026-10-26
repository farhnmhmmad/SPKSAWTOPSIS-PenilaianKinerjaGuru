<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class PerhitunganController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['admin']);
        $penilaian = $this->db->query('SELECT * FROM penilaian ORDER BY tanggal_penilaian DESC')->fetchAll();
        $selected = (int)($_GET['id_penilaian'] ?? 0);
        $metode = $_GET['metode'] ?? '';
        $hasil = [];
        $sawDetail = [];
        $topsisDetail = [];
        $guruMap = [];
        $guruInfo = [];

        if ($selected && $metode) {
            $calc = $this->computeDetail($selected);
            $sawDetail = $calc['sawDetail'] ?? [];
            $topsisDetail = $calc['topsisDetail'] ?? [];
            $guruMap = $calc['guru'] ?? [];
            $guruInfo = $calc['guruInfo'] ?? [];

            if ($metode === 'topsis') {
                $sql = 'SELECT g.nama_guru, g.nip_guru, top.skor_topsis, top.peringkat_topsis, top.status
                        FROM perhitungan_topsis top
                        JOIN guru g ON g.id_guru = top.id_guru
                        WHERE top.id_penilaian = :id
                        ORDER BY g.nama_guru';
            } else {
                $sql = 'SELECT g.nama_guru, g.nip_guru, saw.skor_saw, saw.peringkat_saw, saw.status
                        FROM perhitungan_saw saw
                        JOIN guru g ON g.id_guru = saw.id_guru
                        WHERE saw.id_penilaian = :id
                        ORDER BY g.nama_guru';
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $selected]);
            $hasil = $stmt->fetchAll();
        }

        $history = $this->fetchHistoryAll($penilaian);
        $this->render('perhitungan/index', compact(
            'penilaian',
            'selected',
            'hasil',
            'metode',
            'sawDetail',
            'topsisDetail',
            'guruMap',
            'guruInfo',
            'history'
        ));
    }

    public function proses(): void
    {
        Auth::requireRole(['admin']);
        $idPenilaian = (int)($_POST['id_penilaian'] ?? 0);
        $metode = $_POST['metode'] ?? '';
        if (!$idPenilaian || !in_array($metode, ['saw', 'topsis'], true)) {
            $this->redirect('/perhitungan');
        }
        $cekAll = $this->db->prepare('SELECT 
                (SELECT COUNT(*) FROM perhitungan_saw WHERE id_penilaian=:id1 AND status="published") +
                (SELECT COUNT(*) FROM perhitungan_topsis WHERE id_penilaian=:id2 AND status="published") AS total');
        $cekAll->execute([':id1' => $idPenilaian, ':id2' => $idPenilaian]);
        if ((int)$cekAll->fetchColumn() > 0) {
            $this->redirect('/perhitungan?err=archive_all&id_penilaian=' . $idPenilaian);
        }

        $calc = $this->computeDetail($idPenilaian);
        if (!$calc) $this->redirect('/perhitungan');

        $guru = $calc['guru'];
        $matrix = $calc['matrix'];
        $kriteriaInfo = $calc['kriteria'];
        $max = $min = [];
        foreach ($kriteriaInfo as $kid => $info) {
            $vals = [];
            foreach ($matrix as $gVals) {
                if (isset($gVals[$kid])) $vals[] = $gVals[$kid];
            }
            $max[$kid] = $vals ? max($vals) : 0;
            $min[$kid] = $vals ? min($vals) : 0;
        }
        $normSaw = [];
        $weightedSaw = [];
        $scoreSaw = [];
        foreach ($matrix as $gid => $gVals) {
            $sum = 0;
            foreach ($kriteriaInfo as $kid => $info) {
                $val = $gVals[$kid] ?? 0;
                $norm = $info['tipe'] === 'cost'
                    ? (($val > 0 && $min[$kid] > 0) ? ($min[$kid] / $val) : 0)
                    : ($max[$kid] > 0 ? ($val / $max[$kid]) : 0);
                $normSaw[$gid][$kid] = $norm;
                $weightedSaw[$gid][$kid] = $norm * $info['bobot'];
                $sum += $weightedSaw[$gid][$kid];
            }
            $scoreSaw[$gid] = $sum;
        }
        $denom = [];
        foreach ($kriteriaInfo as $kid => $info) {
            $sumSq = 0;
            foreach ($matrix as $gVals) {
                $v = $gVals[$kid] ?? 0;
                $sumSq += $v * $v;
            }
            $denom[$kid] = $sumSq > 0 ? sqrt($sumSq) : 0;
        }
        $normTopsis = [];
        $weighted = [];
        foreach ($matrix as $gid => $gVals) {
            foreach ($kriteriaInfo as $kid => $info) {
                $norm = $denom[$kid] > 0 ? ($gVals[$kid] ?? 0) / $denom[$kid] : 0;
                $normTopsis[$gid][$kid] = $norm;
                $weighted[$gid][$kid] = $norm * $info['bobot'];
            }
        }
        $idealPlus = $idealMin = [];
        foreach ($kriteriaInfo as $kid => $info) {
            $vals = array_column($weighted, $kid);
            $vals = array_map(fn($v) => $v ?? 0, $vals);
            $idealPlus[$kid] = $info['tipe'] === 'cost' ? min($vals) : max($vals);
            $idealMin[$kid] = $info['tipe'] === 'cost' ? max($vals) : min($vals);
        }
        $scoreTopsis = [];
        foreach ($weighted as $gid => $gVals) {
            $dPlus = 0; $dMin = 0;
            foreach ($kriteriaInfo as $kid => $info) {
                $v = $gVals[$kid] ?? 0;
                $dPlus += ($v - $idealPlus[$kid]) ** 2;
                $dMin  += ($v - $idealMin[$kid]) ** 2;
            }
            $dPlus = sqrt($dPlus);
            $dMin  = sqrt($dMin);
            $pref = ($dPlus + $dMin) > 0 ? $dMin / ($dPlus + $dMin) : 0;
            $scoreTopsis[$gid] = ['d_plus' => $dPlus, 'd_min' => $dMin, 'pref' => $pref];
        }
        $peringkatSaw = array_keys($scoreSaw);
        usort($peringkatSaw, fn($a, $b) => $scoreSaw[$b] <=> $scoreSaw[$a]);
        $peringkatSawMap = [];
        foreach ($peringkatSaw as $idx => $gid) $peringkatSawMap[$gid] = $idx + 1;

        $peringkatTop = array_keys($scoreTopsis);
        usort($peringkatTop, fn($a, $b) => ($scoreTopsis[$b]['pref'] ?? 0) <=> ($scoreTopsis[$a]['pref'] ?? 0));
        $peringkatTopMap = [];
        foreach ($peringkatTop as $idx => $gid) $peringkatTopMap[$gid] = $idx + 1;
        $this->db->beginTransaction();
        if ($metode === 'saw') {
            $this->db->prepare('DELETE FROM perhitungan_saw WHERE id_penilaian=:id')->execute([':id' => $idPenilaian]);
            $insertSaw = $this->db->prepare('INSERT INTO perhitungan_saw (id_penilaian,id_guru,skor_saw,peringkat_saw,status) VALUES (:p,:g,:saw,:psaw,"published")');
            foreach ($guru as $gid => $nama) {
                $insertSaw->execute([
                    ':p' => $idPenilaian,
                    ':g' => $gid,
                    ':saw' => $scoreSaw[$gid] ?? 0,
                    ':psaw' => $peringkatSawMap[$gid] ?? null,
                ]);
            }
        } else {
            $this->db->prepare('DELETE FROM perhitungan_topsis WHERE id_penilaian=:id')->execute([':id' => $idPenilaian]);
            $insertTop = $this->db->prepare('INSERT INTO perhitungan_topsis (id_penilaian,id_guru,skor_topsis,jarak_positif,jarak_negatif,peringkat_topsis,status) VALUES (:p,:g,:top,:dplus,:dmin,:ptop,"published")');
            foreach ($guru as $gid => $nama) {
                $insertTop->execute([
                    ':p' => $idPenilaian,
                    ':g' => $gid,
                    ':top' => $scoreTopsis[$gid]['pref'] ?? 0,
                    ':dplus' => $scoreTopsis[$gid]['d_plus'] ?? 0,
                    ':dmin' => $scoreTopsis[$gid]['d_min'] ?? 0,
                    ':ptop' => $peringkatTopMap[$gid] ?? null,
                ]);
            }
        }
        $this->db->commit();
        $penilaianList = $this->db->query('SELECT * FROM penilaian ORDER BY tanggal_penilaian DESC')->fetchAll();
        $sql = 'SELECT g.nama_guru, g.nip_guru,
                       saw.skor_saw, saw.peringkat_saw,
                       top.skor_topsis, top.peringkat_topsis
                FROM perhitungan_saw saw
                JOIN guru g ON g.id_guru = saw.id_guru
                LEFT JOIN perhitungan_topsis top ON top.id_penilaian = saw.id_penilaian AND top.id_guru = saw.id_guru
                WHERE saw.id_penilaian = :id
                ORDER BY g.nama_guru';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idPenilaian]);
        $hasil = $stmt->fetchAll();
        $history = $this->fetchHistoryAll($penilaianList);

        $this->render('perhitungan/index', [
            'penilaian' => $penilaianList,
            'selected' => $idPenilaian,
            'hasil' => $hasil,
            'metode' => $metode,
            'sawDetail' => $calc['sawDetail'] ?? [],
            'topsisDetail' => $calc['topsisDetail'] ?? [],
            'guruMap' => $guru,
            'guruInfo' => $calc['guruInfo'] ?? [],
            'history' => $history,
        ]);
    }

    public function cetak(): void
    {
        Auth::requireRole(['admin']);
        $idPenilaian = (int)($_GET['id_penilaian'] ?? 0);
        $metode = $_GET['metode'] ?? '';
        if (!$idPenilaian || !in_array($metode, ['saw', 'topsis'], true)) {
            $this->redirect('/perhitungan');
        }
        $penilaian = $this->db->prepare('SELECT * FROM penilaian WHERE id_penilaian=:id');
        $penilaian->execute([':id' => $idPenilaian]);
        $pen = $penilaian->fetch();
        if (!$pen) {
            $this->redirect('/perhitungan');
        }
        if ($metode === 'saw') {
            $cek = $this->db->prepare('SELECT COUNT(*) FROM perhitungan_saw WHERE id_penilaian=:id AND status="published"');
            $cek->execute([':id' => $idPenilaian]);
            if ($cek->fetchColumn() == 0) {
                $this->redirect('/perhitungan?err=publish_saw&id_penilaian=' . $idPenilaian);
            }
        } else {
            $cek = $this->db->prepare('SELECT COUNT(*) FROM perhitungan_topsis WHERE id_penilaian=:id AND status="published"');
            $cek->execute([':id' => $idPenilaian]);
            if ($cek->fetchColumn() == 0) {
                $this->redirect('/perhitungan?err=publish_topsis&id_penilaian=' . $idPenilaian);
            }
        }

        $calc = $this->computeDetail($idPenilaian);
        $sawDetail = $calc['sawDetail'] ?? [];
        $topsisDetail = $calc['topsisDetail'] ?? [];
        $guruMap = $calc['guru'] ?? [];
        $guruInfo = $calc['guruInfo'] ?? [];

        $this->render('perhitungan/cetak', [
            'penilaian' => $pen,
            'metode' => $metode,
            'sawDetail' => $sawDetail,
            'topsisDetail' => $topsisDetail,
            'guruMap' => $guruMap,
            'guruInfo' => $guruInfo,
        ]);
    }

    public function updateStatus(): void
    {
        Auth::requireRole(['admin']);
        $idPenilaian = (int)($_POST['id_penilaian'] ?? 0);
        $metode = $_POST['metode'] ?? '';
        $action = $_POST['action'] ?? '';
        if (!$idPenilaian || !in_array($metode, ['saw', 'topsis'], true) || !in_array($action, ['publish', 'archive'], true)) {
            $this->redirect('/perhitungan');
        }
        $isSaw = $metode === 'saw';
        $targetTable = $isSaw ? 'perhitungan_saw' : 'perhitungan_topsis';
        $otherTable = $isSaw ? 'perhitungan_topsis' : 'perhitungan_saw';

        if ($action === 'publish') {
            $cek = $this->db->prepare("SELECT COUNT(*) FROM {$otherTable} WHERE id_penilaian=:id AND status='published'");
            $cek->execute([':id' => $idPenilaian]);
            if ($cek->fetchColumn() > 0) {
                $this->redirect('/perhitungan?err=must_archive_other&id_penilaian=' . $idPenilaian);
            }
            $this->db->prepare("UPDATE {$targetTable} SET status='published' WHERE id_penilaian=:id")->execute([':id' => $idPenilaian]);
            $this->db->prepare("UPDATE {$otherTable} SET status='archived' WHERE id_penilaian=:id")->execute([':id' => $idPenilaian]);
        } else {
            $this->db->prepare("UPDATE {$targetTable} SET status='archived' WHERE id_penilaian=:id")->execute([':id' => $idPenilaian]);
        }
        $this->redirect('/perhitungan?id_penilaian=' . $idPenilaian);
    }

    private function fetchHistoryAll(array $penilaianList): array
    {
        $map = [];
        foreach ($penilaianList as $p) {
            $map[$p['id_penilaian']] = [
                'penilaian' => $p,
                'saw' => null,
                'topsis' => null,
            ];
        }

        $sqlSaw = "SELECT p1.id_penilaian, p1.status, p1.created_at
                   FROM perhitungan_saw p1
                   JOIN (SELECT id_penilaian, MAX(created_at) mc FROM perhitungan_saw GROUP BY id_penilaian) m
                     ON m.id_penilaian = p1.id_penilaian AND m.mc = p1.created_at";
        foreach ($this->db->query($sqlSaw)->fetchAll() as $row) {
            $pid = (int)$row['id_penilaian'];
            if (isset($map[$pid])) $map[$pid]['saw'] = $row;
        }

        $sqlTop = "SELECT p1.id_penilaian, p1.status, p1.created_at
                   FROM perhitungan_topsis p1
                   JOIN (SELECT id_penilaian, MAX(created_at) mc FROM perhitungan_topsis GROUP BY id_penilaian) m
                     ON m.id_penilaian = p1.id_penilaian AND m.mc = p1.created_at";
        foreach ($this->db->query($sqlTop)->fetchAll() as $row) {
            $pid = (int)$row['id_penilaian'];
            if (isset($map[$pid])) $map[$pid]['topsis'] = $row;
        }

        return $map;
    }

    private function percentToScore(float $percent): float
    {
        if ($percent <= 0) return 0;
        if ($percent <= 25) return 1;
        if ($percent <= 50) return 2;
        if ($percent <= 75) return 3;
        return 4;
    }

    private function computeDetail(int $idPenilaian): array
    {
        if (!$idPenilaian) return [];
        $sql = "SELECT agg.id_guru, agg.id_subkriteria, agg.total_nilai, agg.skor_maks, s.id_kriteria, g.nama_guru, g.nip_guru,
                       k.bobot_kriteria, k.tipe_kriteria, k.nama_kriteria
                FROM (
                    SELECT pd.id_guru,
                           i.id_subkriteria,
                           SUM(pd.nilai) AS total_nilai,
                           COUNT(*) * 2 AS skor_maks
                    FROM penilaian_detail pd
                    JOIN indikator i ON i.id_indikator = pd.id_indikator
                    WHERE pd.id_penilaian = :id
                    GROUP BY pd.id_guru, i.id_subkriteria
                ) agg
                JOIN subkriteria s ON s.id_subkriteria = agg.id_subkriteria
                JOIN kriteria k ON k.id_kriteria = s.id_kriteria
                JOIN guru g ON g.id_guru = agg.id_guru
                ORDER BY g.nama_guru, s.id_kriteria, agg.id_subkriteria";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idPenilaian]);
        $rows = $stmt->fetchAll();
        if (!$rows) return [];

        $guru = [];
        $guruInfo = [];
        $kriteriaInfo = [];
        $subVals = [];
        foreach ($rows as $r) {
            $gid = (int)$r['id_guru'];
            $kid = (int)$r['id_kriteria'];
            $nama = $r['nama_guru'];
            $nip = $r['nip_guru'] ?? '';
            $guru[$gid] = $nama;
            $guruInfo[$gid] = ['nip' => $nip, 'nama' => $nama];
            $kriteriaInfo[$kid] = [
                'bobot' => (float)$r['bobot_kriteria'],
                'tipe' => $r['tipe_kriteria'],
                'nama' => $r['nama_kriteria'] ?? ('K' . $kid),
            ];
            $total = (float)$r['total_nilai'];
            $max = (float)$r['skor_maks'];
            $percent = $max > 0 ? ($total / $max) * 100 : 0;
            $val = $this->percentToScore($percent);
            $subVals[$gid][$kid][] = ['val' => $val, 'percent' => $percent, 'total' => $total, 'max' => $max];
        }

        $matrix = [];
        foreach ($guru as $gid => $_) {
            foreach ($kriteriaInfo as $kid => $_k) {
                $vals = $subVals[$gid][$kid] ?? [];
                $matrix[$gid][$kid] = $vals ? array_sum(array_column($vals, 'val')) / count($vals) : 0;
            }
        }

        $totalBobot = array_sum(array_column($kriteriaInfo, 'bobot'));
        if ($totalBobot > 0) {
            foreach ($kriteriaInfo as $k => $info) {
                $kriteriaInfo[$k]['bobot'] = $info['bobot'] / $totalBobot;
            }
        }
        $max = $min = [];
        foreach ($kriteriaInfo as $kid => $info) {
            $vals = [];
            foreach ($matrix as $gVals) {
                if (isset($gVals[$kid])) $vals[] = $gVals[$kid];
            }
            $max[$kid] = $vals ? max($vals) : 0;
            $min[$kid] = $vals ? min($vals) : 0;
        }
        $normSaw = [];
        $weightedSaw = [];
        $scoreSaw = [];
        foreach ($matrix as $gid => $gVals) {
            $sum = 0;
            foreach ($kriteriaInfo as $kid => $info) {
                $val = $gVals[$kid] ?? 0;
                $norm = $info['tipe'] === 'cost'
                    ? (($val > 0 && $min[$kid] > 0) ? ($min[$kid] / $val) : 0)
                    : ($max[$kid] > 0 ? ($val / $max[$kid]) : 0);
                $normSaw[$gid][$kid] = $norm;
                $weightedSaw[$gid][$kid] = $norm * $info['bobot'];
                $sum += $weightedSaw[$gid][$kid];
            }
            $scoreSaw[$gid] = $sum;
        }
        $peringkatSaw = array_keys($scoreSaw);
        usort($peringkatSaw, fn($a, $b) => $scoreSaw[$b] <=> $scoreSaw[$a]);
        $peringkatSawMap = [];
        foreach ($peringkatSaw as $idx => $gid) $peringkatSawMap[$gid] = $idx + 1;
        $denom = [];
        foreach ($kriteriaInfo as $kid => $info) {
            $sumSq = 0;
            foreach ($matrix as $gVals) {
                $v = $gVals[$kid] ?? 0;
                $sumSq += $v * $v;
            }
            $denom[$kid] = $sumSq > 0 ? sqrt($sumSq) : 0;
        }
        $normTopsis = [];
        $weighted = [];
        foreach ($matrix as $gid => $gVals) {
            foreach ($kriteriaInfo as $kid => $info) {
                $norm = $denom[$kid] > 0 ? ($gVals[$kid] ?? 0) / $denom[$kid] : 0;
                $normTopsis[$gid][$kid] = $norm;
                $weighted[$gid][$kid] = $norm * $info['bobot'];
            }
        }
        $idealPlus = $idealMin = [];
        foreach ($kriteriaInfo as $kid => $info) {
            $vals = array_column($weighted, $kid);
            $vals = array_map(fn($v) => $v ?? 0, $vals);
            $idealPlus[$kid] = $info['tipe'] === 'cost' ? min($vals) : max($vals);
            $idealMin[$kid] = $info['tipe'] === 'cost' ? max($vals) : min($vals);
        }
        $scoreTopsis = [];
        foreach ($weighted as $gid => $gVals) {
            $dPlus = 0; $dMin = 0;
            foreach ($kriteriaInfo as $kid => $info) {
                $v = $gVals[$kid] ?? 0;
                $dPlus += ($v - $idealPlus[$kid]) ** 2;
                $dMin += ($v - $idealMin[$kid]) ** 2;
            }
            $dPlus = sqrt($dPlus);
            $dMin = sqrt($dMin);
            $pref = ($dPlus + $dMin) > 0 ? $dMin / ($dPlus + $dMin) : 0;
            $scoreTopsis[$gid] = ['d_plus' => $dPlus, 'd_min' => $dMin, 'pref' => $pref];
        }
        $peringkatTop = array_keys($scoreTopsis);
        usort($peringkatTop, fn($a, $b) => ($scoreTopsis[$b]['pref'] ?? 0) <=> ($scoreTopsis[$a]['pref'] ?? 0));
        $peringkatTopMap = [];
        foreach ($peringkatTop as $idx => $gid) $peringkatTopMap[$gid] = $idx + 1;

        $sawDetail = [
            'matrix' => $matrix,
            'norm' => $normSaw,
            'weighted' => $weightedSaw,
            'max' => $max,
            'min' => $min,
            'bobot' => $kriteriaInfo,
            'score' => $scoreSaw,
            'peringkat' => $peringkatSawMap,
            'sub' => $subVals,
        ];
        $topsisDetail = [
            'matrix' => $matrix,
            'norm' => $normTopsis,
            'norm_weighted' => $weighted,
            'bobot' => $kriteriaInfo,
            'ideal_plus' => $idealPlus,
            'ideal_min' => $idealMin,
            'score' => $scoreTopsis,
            'peringkat' => $peringkatTopMap,
            'sub' => $subVals,
        ];

        return [
            'guru' => $guru,
            'guruInfo' => $guruInfo,
            'kriteria' => $kriteriaInfo,
            'matrix' => $matrix,
            'sawDetail' => $sawDetail,
            'topsisDetail' => $topsisDetail,
        ];
    }
}
