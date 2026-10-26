<?php
$title = 'Cetak Detail Penilaian';
$printedBy = $_SESSION['user']['username'] ?? 'Guest';
$printedAt = date('Y-m-d');
$valueLabels = [
  0 => '0 - Tidak Terpenuhi',
  1 => '1 - Terpenuhi Sebagian',
  2 => '2 - Seluruhnya Terpenuhi'
];
$subNames = [];
foreach ($sub as $s) { $subNames[$s['id_subkriteria']] = $s['nama_subkriteria']; }
$kriteriaNames = [];
foreach (($kriteria ?? []) as $k) { $kriteriaNames[$k['id_kriteria']] = $k['nama_kriteria'] ?? ('Kriteria '.$k['id_kriteria']); }
$subByKriteria = [];
foreach ($sub as $s) {
  $subByKriteria[$s['id_kriteria']][] = $s;
}
$indikatorBySub = [];
foreach ($indikator as $i) {
  $indikatorBySub[$i['id_subkriteria']][] = $i;
}
$curPeriod = $penilaian['periode'] ?? '-';
$curNama   = $penilaian['nama_penilai'] ?? '';
$curNip    = $penilaian['nip_penilai'] ?? '';
$curTgl    = substr($penilaian['tanggal_penilaian'] ?? '-', 0, 10);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($title) ?></title>
  <style>
    *{box-sizing:border-box;}
    body{font-family:Georgia,"Times New Roman",Arial,sans-serif;margin:18mm;background:#fff;color:#111;}
    .wrap{max-width:900px;margin:0 auto;}
    h2{margin:0 0 6px;font-size:22px;}
    h4{margin:8px 0 6px;font-size:16px;}
    .meta{color:#555;font-size:13px;margin-bottom:10px;}
    table{width:100%;border-collapse:collapse;margin-bottom:14px;font-size:13px;}
    th,td{border:1px solid #777;padding:8px;vertical-align:top;}
    th{background:#f2f4f7;font-weight:700;}
    .btn-print{display:inline-block;padding:8px 12px;border:1px solid #0d6efd;border-radius:6px;color:#0d6efd;text-decoration:none;font-weight:700;margin-bottom:16px;}
    thead{display:table-header-group;}
    tr{page-break-inside:avoid;}
    @page{size:A4 portrait;margin:16mm;}
    @media print{.no-print{display:none !important;} body{margin:10mm;} .wrap{max-width:none;}}
    .header-grid{display:grid;grid-template-columns:120px 1fr auto;gap:12px;align-items:center;margin-bottom:10px;}
    .logo img{max-width:110px;max-height:80px;object-fit:contain;}
    .title-block h2{margin:0;font-size:22px;}
    .title-block .meta-line{font-size:12px;color:#444;margin-top:2px;}
    .sub-title{font-weight:700;background:#f8fafc;}
  </style>
</head>
<body>
<div class="wrap">
  <div class="header-grid">
    <div class="logo"><img src="/uploads/logo-sekolah.png" alt="Logo" onerror="this.style.display='none'"></div>
    <div class="title-block">
      <h2>Cetak Detail Penilaian</h2>
      <div class="meta-line">Periode: <?= htmlspecialchars($curPeriod) ?> | Penilai: <?= $curNip !== '' ? htmlspecialchars($curNip).' / ' : '' ?><?= htmlspecialchars($curNama ?: '-') ?> (<?= htmlspecialchars($curTgl) ?>)</div>
      <div class="meta-line">Guru: <?= htmlspecialchars($guru['nip_guru'] ?? '') ?><?= !empty($guru['nip_guru']) ? ' / ' : '' ?><?= htmlspecialchars($guru['nama_guru'] ?? '') ?></div>
      <div class="meta-line">Tanggal Cetak: <?= htmlspecialchars($printedAt) ?> &nbsp;|&nbsp; Dibuat oleh: <?= htmlspecialchars($printedBy) ?></div>
    </div>
    <div style="text-align:right;">
      <a class="btn-print no-print" href="#" onclick="window.print();return false;">Print / PDF</a>
    </div>
  </div>

  <?php if($sub): ?>
    <table>
      <thead>
        <tr>
          <th style="width:40px;">No</th>
          <th style="width:220px;">Sub-Kriteria</th>
          <th>Indikator</th>
          <th style="width:180px;">Nilai</th>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1; foreach(($kriteria ?? []) as $k): ?>
          <?php $kid = (int)$k['id_kriteria']; ?>
          <tr class="sub-title">
            <td colspan="4"><?= htmlspecialchars($kriteriaNames[$kid] ?? ('Kriteria '.$kid)) ?></td>
          </tr>
          <?php $hasRow = false; ?>
          <?php foreach(($subByKriteria[$kid] ?? []) as $s): ?>
            <?php if(!empty($indikatorBySub[$s['id_subkriteria']])): ?>
              <?php foreach($indikatorBySub[$s['id_subkriteria']] as $i): ?>
                <?php $hasRow = true; $val = $detail[$i['id_indikator']] ?? null; ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td><?= htmlspecialchars($subNames[$s['id_subkriteria']] ?? 'Subkriteria') ?></td>
                  <td><?= htmlspecialchars($i['nama_indikator'] ?? '') ?></td>
                  <td><?= is_null($val) ? 'Belum dinilai' : ($valueLabels[$val] ?? ('Nilai '.$val)) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          <?php endforeach; ?>
          <?php if(!$hasRow): ?>
            <tr>
              <td colspan="4" style="color:#666;font-style:italic;">Belum ada indikator</td>
            </tr>
          <?php endif; ?>
          <?php $firstSubId = 0; if (!empty($subByKriteria[$kid])) { $firstSubId = (int)($subByKriteria[$kid][0]['id_subkriteria'] ?? 0); } ?>
          <tr>
            <td colspan="4">
              <div style="font-size:12px;color:#666;">Catatan Bukti Penilaian</div>
              <div style="font-weight:700;"><?= htmlspecialchars($catatanMap[$firstSubId] ?? '-') ?></div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div style="padding:12px;border:1px solid #ddd;background:#f8fafc;">Belum ada data untuk dicetak.</div>
  <?php endif; ?>
</div>
</body>
</html>
