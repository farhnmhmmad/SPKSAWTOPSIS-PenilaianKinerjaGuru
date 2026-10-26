<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['admin','penilai']);
        $user = Auth::user();
        $counts = [
            'guru' => (int)$this->db->query('SELECT COUNT(*) FROM guru')->fetchColumn(),
            'kriteria' => (int)$this->db->query('SELECT COUNT(*) FROM kriteria')->fetchColumn(),
            'subkriteria' => (int)$this->db->query('SELECT COUNT(*) FROM subkriteria')->fetchColumn(),
            'penilaian' => (int)$this->db->query('SELECT COUNT(*) FROM penilaian')->fetchColumn(),
        ];
        $penilaianList = $this->db->query('SELECT * FROM penilaian ORDER BY tanggal_penilaian DESC')->fetchAll();
        $latestPenilaian = $penilaianList[0] ?? null;
        $selectedPenilaianId = (int)($_GET['id_penilaian'] ?? ($latestPenilaian['id_penilaian'] ?? 0));

        $top5 = [];
        $top5Method = '';
        if ($selectedPenilaianId) {
            $cekSaw = $this->db->prepare('SELECT COUNT(*) FROM perhitungan_saw WHERE id_penilaian=:id AND status="published"');
            $cekSaw->execute([':id'=>$selectedPenilaianId]);
            $hasSaw = (int)$cekSaw->fetchColumn() > 0;
            $cekTop = $this->db->prepare('SELECT COUNT(*) FROM perhitungan_topsis WHERE id_penilaian=:id AND status="published"');
            $cekTop->execute([':id'=>$selectedPenilaianId]);
            $hasTop = (int)$cekTop->fetchColumn() > 0;

            if ($hasSaw) {
                $top5Method = 'saw';
                $stmt = $this->db->prepare("SELECT g.id_guru, g.nama_guru, saw.skor_saw
                                            FROM perhitungan_saw saw
                                            JOIN guru g ON g.id_guru = saw.id_guru
                                            WHERE saw.id_penilaian = :id AND saw.status='published'
                                            ORDER BY saw.peringkat_saw ASC
                                            LIMIT 5");
                $stmt->execute([':id'=>$selectedPenilaianId]);
                $top5 = $stmt->fetchAll();
            } elseif ($hasTop) {
                $top5Method = 'topsis';
                $stmt = $this->db->prepare("SELECT g.id_guru, g.nama_guru, top.skor_topsis
                                            FROM perhitungan_topsis top
                                            JOIN guru g ON g.id_guru = top.id_guru
                                            WHERE top.id_penilaian = :id AND top.status='published'
                                            ORDER BY top.peringkat_topsis ASC
                                            LIMIT 5");
                $stmt->execute([':id'=>$selectedPenilaianId]);
                $top5 = $stmt->fetchAll();
            }
        }

        $guruList = $this->db->query('SELECT id_guru, nama_guru, nip_guru FROM guru ORDER BY nama_guru ASC')->fetchAll();
        $selectedGuruId = (int)($_GET['id_guru'] ?? 0);
        if (!$selectedGuruId && !empty($top5[0]['id_guru'])) {
            $selectedGuruId = (int)$top5[0]['id_guru'];
        }
        if (!$selectedGuruId && !empty($guruList[0]['id_guru'])) {
            $selectedGuruId = (int)$guruList[0]['id_guru'];
        }

        $historyLabels = [];
        $historySaw = [];
        $historyTopsis = [];
        $historyCombined = [];
        if ($selectedGuruId) {
            $stmtSaw = $this->db->prepare("SELECT p.id_penilaian, p.periode, p.tanggal_penilaian, saw.peringkat_saw
                                           FROM perhitungan_saw saw
                                           JOIN penilaian p ON p.id_penilaian = saw.id_penilaian
                                           WHERE saw.id_guru = :gid AND saw.status='published'
                                           ORDER BY p.tanggal_penilaian ASC");
            $stmtSaw->execute([':gid'=>$selectedGuruId]);
            $rowsSaw = $stmtSaw->fetchAll();

            $stmtTop = $this->db->prepare("SELECT p.id_penilaian, p.periode, p.tanggal_penilaian, top.peringkat_topsis
                                           FROM perhitungan_topsis top
                                           JOIN penilaian p ON p.id_penilaian = top.id_penilaian
                                           WHERE top.id_guru = :gid AND top.status='published'
                                           ORDER BY p.tanggal_penilaian ASC");
            $stmtTop->execute([':gid'=>$selectedGuruId]);
            $rowsTop = $stmtTop->fetchAll();

            $periods = [];
            $historySawMap = [];
            $historyTopsisMap = [];
            foreach ($rowsSaw as $r) {
                $pid = (int)$r['id_penilaian'];
                $periods[$pid] = [
                    'periode' => $r['periode'] ?? '',
                    'tanggal' => $r['tanggal_penilaian'] ?? '',
                ];
                $historySawMap[$pid] = (int)($r['peringkat_saw'] ?? 0);
            }
            foreach ($rowsTop as $r) {
                $pid = (int)$r['id_penilaian'];
                if (!isset($periods[$pid])) {
                    $periods[$pid] = [
                        'periode' => $r['periode'] ?? '',
                        'tanggal' => $r['tanggal_penilaian'] ?? '',
                    ];
                }
                $historyTopsisMap[$pid] = (int)($r['peringkat_topsis'] ?? 0);
            }

            uasort($periods, fn($a,$b) => strcmp($a['tanggal'], $b['tanggal']));
            foreach ($periods as $pid => $meta) {
                $historyLabels[] = $meta['periode'];
                $sawVal = $historySawMap[$pid] ?? null;
                $topVal = $historyTopsisMap[$pid] ?? null;
                $historySaw[] = $sawVal;
                $historyTopsis[] = $topVal;
                $historyCombined[] = $sawVal ?? $topVal ?? null;
            }
        }

        $top5Labels = array_map(fn($r) => $r['nama_guru'] ?? '', $top5);
        $top5Scores = array_map(fn($r) => (float)($r['skor_saw'] ?? $r['skor_topsis'] ?? 0), $top5);

        $this->render('dashboard/index', compact(
            'user',
            'counts',
            'penilaianList',
            'latestPenilaian',
            'selectedPenilaianId',
            'top5',
            'top5Method',
            'top5Labels',
            'top5Scores',
            'guruList',
            'selectedGuruId',
            'historyLabels',
            'historySaw',
            'historyTopsis',
            'historyCombined'
        ));
    }
}
