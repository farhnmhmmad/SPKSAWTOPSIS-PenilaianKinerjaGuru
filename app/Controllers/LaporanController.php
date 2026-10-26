<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class LaporanController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $user = Auth::user();
        $kriteria = $this->db->query('SELECT id_kriteria, nama_kriteria FROM kriteria ORDER BY id_kriteria')->fetchAll();
        $penilaian = $this->db->query('SELECT * FROM penilaian ORDER BY tanggal_penilaian DESC')->fetchAll();
        $selected = (int)($_GET['id_penilaian'] ?? ($penilaian[0]['id_penilaian'] ?? 0));
        $metode = $_GET['metode'] ?? '';
        $detail = [];
        $radar = [];
        $trend = [];
        $guruMap = [];

        $whereUser = '';
        $paramsBase = [];
        if ($user && $user['role'] === 'guru' && !empty($user['id_guru'])) {
            $whereUser = ' AND pd.id_guru = :gid';
            $paramsBase[':gid'] = (int)$user['id_guru'];
        }

        if ($selected) {
            $params = array_merge([':id'=>$selected], $paramsBase);
            if ($metode === 'saw') {
                $sqlDetail = "SELECT g.id_guru, g.nama_guru, ps.skor_saw AS skor, ps.rank_saw AS rank
                              FROM perhitungan_saw ps
                              JOIN guru g ON g.id_guru = ps.id_guru
                              WHERE ps.id_penilaian = :id" . ($whereUser ? " AND ps.id_guru = :gid" : "") . "
                              ORDER BY g.nama_guru";
                $stmt = $this->db->prepare($sqlDetail);
                $stmt->execute($params);
                $detail = $stmt->fetchAll();
            } elseif ($metode === 'topsis') {
                $sqlDetail = "SELECT g.id_guru, g.nama_guru, pt.skor_topsis AS skor, pt.rank_topsis AS rank
                              FROM perhitungan_topsis pt
                              JOIN guru g ON g.id_guru = pt.id_guru
                              WHERE pt.id_penilaian = :id" . ($whereUser ? " AND pt.id_guru = :gid" : "") . "
                              ORDER BY g.nama_guru";
                $stmt = $this->db->prepare($sqlDetail);
                $stmt->execute($params);
                $detail = $stmt->fetchAll();
            }
            foreach ($detail as $d) {
                $gid = (int)$d['id_guru'];
                $guruMap[$gid] = $d['nama_guru'];
            }
            $sqlRadar = "SELECT pd.id_guru, g.nama_guru, k.id_kriteria, k.nama_kriteria,
                                (SUM(pd.nilai) / NULLIF(COUNT(*)*2,0)) * 100 AS persentase
                         FROM penilaian_detail pd
                         JOIN indikator i ON i.id_indikator = pd.id_indikator
                         JOIN subkriteria s ON s.id_subkriteria = i.id_subkriteria
                         JOIN kriteria k ON k.id_kriteria = s.id_kriteria
                         JOIN guru g ON g.id_guru = pd.id_guru
                         WHERE pd.id_penilaian = :id {$whereUser}
                         GROUP BY pd.id_guru, k.id_kriteria
                         ORDER BY g.nama_guru, k.id_kriteria";
            $stmt = $this->db->prepare($sqlRadar);
            $stmt->execute($params);
            foreach ($stmt->fetchAll() as $r) {
                $gid = (int)$r['id_guru'];
                $guruMap[$gid] = $r['nama_guru'];
                $radar[$gid]['label'] = $r['nama_guru'];
                $radar[$gid]['values'][$r['nama_kriteria']] = (float)$r['persentase'];
            }
            if ($metode === 'saw') {
                $sqlTrend = "SELECT ps.id_guru, g.nama_guru, ps.id_penilaian, p.nama_penilaian, p.tanggal_penilaian, ps.skor_saw AS skor, ps.rank_saw AS rank
                             FROM perhitungan_saw ps
                             JOIN penilaian p ON p.id_penilaian = ps.id_penilaian
                             JOIN guru g ON g.id_guru = ps.id_guru
                             WHERE 1=1 {$whereUser}
                             ORDER BY g.nama_guru, p.tanggal_penilaian";
                $stmt = $this->db->prepare($sqlTrend);
                $stmt->execute($paramsBase);
                $trend = $stmt->fetchAll();
            } elseif ($metode === 'topsis') {
                $sqlTrend = "SELECT pt.id_guru, g.nama_guru, pt.id_penilaian, p.nama_penilaian, p.tanggal_penilaian, pt.skor_topsis AS skor, pt.rank_topsis AS rank
                             FROM perhitungan_topsis pt
                             JOIN penilaian p ON p.id_penilaian = pt.id_penilaian
                             JOIN guru g ON g.id_guru = pt.id_guru
                             WHERE 1=1 {$whereUser}
                             ORDER BY g.nama_guru, p.tanggal_penilaian";
                $stmt = $this->db->prepare($sqlTrend);
                $stmt->execute($paramsBase);
                $trend = $stmt->fetchAll();
            }
        }
        $this->render('laporan/index', [
            'penilaian'=>$penilaian,
            'selected'=>$selected,
            'detail'=>$detail,
            'metode'=>$metode,
            'radar'=>$radar,
            'trend'=>$trend,
            'guruMap'=>$guruMap,
            'currentUser'=>$user,
            'kriteria'=>$kriteria,
        ]);
    }
}
