<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class PeringkatController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['admin','penilai','guest']);
        $penilaian = $this->db->query('SELECT * FROM penilaian ORDER BY tanggal_penilaian DESC')->fetchAll();
        $selected = (int)($_GET['id_penilaian'] ?? ($penilaian[0]['id_penilaian'] ?? 0));
        $metode = '';
        $hasil = [];
        $err = '';
        if ($selected) {
            $cekSaw = $this->db->prepare('SELECT COUNT(*) FROM perhitungan_saw WHERE id_penilaian=:id AND status="published"');
            $cekSaw->execute([':id'=>$selected]);
            $hasSaw = (int)$cekSaw->fetchColumn() > 0;
            $cekTop = $this->db->prepare('SELECT COUNT(*) FROM perhitungan_topsis WHERE id_penilaian=:id AND status="published"');
            $cekTop->execute([':id'=>$selected]);
            $hasTop = (int)$cekTop->fetchColumn() > 0;

            if ($hasSaw) {
                $metode = 'saw';
                $order = 'saw.peringkat_saw';
                $sql = "SELECT g.nama_guru, g.nip_guru, saw.skor_saw, saw.peringkat_saw
                        FROM perhitungan_saw saw
                        JOIN guru g ON g.id_guru = saw.id_guru
                        WHERE saw.id_penilaian = :id AND saw.status='published'
                        ORDER BY {$order}";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':id'=>$selected]);
                $hasil = $stmt->fetchAll();
            } elseif ($hasTop) {
                $metode = 'topsis';
                $order = 'top.peringkat_topsis';
                $sql = "SELECT g.nama_guru, g.nip_guru, top.skor_topsis, top.peringkat_topsis
                        FROM perhitungan_topsis top
                        JOIN guru g ON g.id_guru = top.id_guru
                        WHERE top.id_penilaian = :id AND top.status='published'
                        ORDER BY {$order}";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':id'=>$selected]);
                $hasil = $stmt->fetchAll();
            } else {
                $err = 'publish_any';
            }
        }
        $this->render('peringkat/index', compact('penilaian','selected','hasil','metode','err'));
    }

    public function cetak(): void
    {
        Auth::requireRole(['admin','penilai','guest']);
        $penilaian = $this->db->query('SELECT * FROM penilaian ORDER BY tanggal_penilaian DESC')->fetchAll();
        $selected = (int)($_GET['id_penilaian'] ?? ($penilaian[0]['id_penilaian'] ?? 0));
        $hasil = [];
        $metode = '';
        if ($selected) {
            $cekSaw = $this->db->prepare('SELECT COUNT(*) FROM perhitungan_saw WHERE id_penilaian=:id AND status="published"');
            $cekSaw->execute([':id'=>$selected]);
            $hasSaw = (int)$cekSaw->fetchColumn() > 0;
            $cekTop = $this->db->prepare('SELECT COUNT(*) FROM perhitungan_topsis WHERE id_penilaian=:id AND status="published"');
            $cekTop->execute([':id'=>$selected]);
            $hasTop = (int)$cekTop->fetchColumn() > 0;

            if ($hasSaw) {
                $metode = 'saw';
                $stmt = $this->db->prepare("SELECT g.nama_guru, g.nip_guru, saw.skor_saw, saw.peringkat_saw
                                            FROM perhitungan_saw saw
                                            JOIN guru g ON g.id_guru = saw.id_guru
                                            WHERE saw.id_penilaian = :id AND saw.status='published'
                                            ORDER BY saw.peringkat_saw");
                $stmt->execute([':id'=>$selected]);
                $hasil = $stmt->fetchAll();
            } elseif ($hasTop) {
                $metode = 'topsis';
                $stmt = $this->db->prepare("SELECT g.nama_guru, g.nip_guru, top.skor_topsis, top.peringkat_topsis
                                            FROM perhitungan_topsis top
                                            JOIN guru g ON g.id_guru = top.id_guru
                                            WHERE top.id_penilaian = :id AND top.status='published'
                                            ORDER BY top.peringkat_topsis");
                $stmt->execute([':id'=>$selected]);
                $hasil = $stmt->fetchAll();
            } else {
                $this->redirect('/peringkat?err=publish_any&id_penilaian='.$selected);
            }
        }
        $this->render('peringkat/cetak', compact('penilaian','selected','hasil','metode'));
    }
}
