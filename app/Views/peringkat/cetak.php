<?php
$title   = 'Cetak Peringkat';
$rows    = $penilaian ?? [];
$hasil   = $hasil ?? [];
$metode  = $metode ?? '';
$selected = $selected ?? 0;
$printedBy = $_SESSION['user']['username'] ?? 'Guest';
$printedAt = date('Y-m-d');

$current = null;
foreach ($rows as $p) {
    if (($p['id_penilaian'] ?? 0) == $selected) { $current = $p; break; }
}
$current = $current ?: [];
$curPeriod = $current['periode'] ?? '-';
$curNama   = $current['nama_penilai'] ?? '';
$curNip    = $current['nip_penilai'] ?? '';
$curTgl    = substr($current['tanggal_penilaian'] ?? '-', 0, 10);
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
    h4{margin:0 0 8px;font-size:16px;}
    .meta{color:#555;font-size:13px;margin-bottom:10px;}
    table{width:100%;border-collapse:collapse;margin-bottom:14px;font-size:13px;}
    th,td{border:1px solid #777;padding:8px;vertical-align:top;}
    th{background:#f2f4f7;font-weight:700;}
    .btn-print{display:inline-block;padding:8px 12px;border:1px solid #0d6efd;border-radius:6px;color:#0d6efd;text-decoration:none;font-weight:700;margin-bottom:16px;}
    thead{display:table-header-group;}
    tr{page-break-inside:avoid;}
    @page{size:A4 landscape;margin:16mm;}
    @media print{.no-print{display:none !important;} body{margin:10mm;} .wrap{max-width:none;}}
    .header-grid{display:grid;grid-template-columns:120px 1fr auto;gap:12px;align-items:center;margin-bottom:10px;}
    .logo img{max-width:110px;max-height:80px;object-fit:contain;}
    .title-block h2{margin:0;font-size:22px;}
    .title-block .meta-line{font-size:12px;color:#444;margin-top:2px;}
  </style>
</head>
<body>
<div class="wrap">
  <div class="header-grid">
    <div class="logo"><img src="/uploads/logo-sekolah.png" alt="Logo" onerror="this.style.display='none'"></div>
    <div class="title-block">
      <h2>Cetak Peringkat <?= $metode ? strtoupper($metode) : '' ?></h2>
      <div class="meta-line">Periode: <?= htmlspecialchars($curPeriod) ?> | Penilai: <?= $curNip !== '' ? htmlspecialchars($curNip).' / ' : '' ?><?= htmlspecialchars($curNama ?: '-') ?> (<?= htmlspecialchars($curTgl) ?>)</div>
      <div class="meta-line">Tanggal Cetak: <?= htmlspecialchars($printedAt) ?> &nbsp;|&nbsp; Dibuat oleh: <?= htmlspecialchars($printedBy) ?></div>
    </div>
    <div style="text-align:right;">
      <a class="btn-print no-print" href="#" onclick="window.print();return false;"><i class="bi bi-printer me-1"></i>Print / PDF</a>
    </div>
  </div>

  <?php if($hasil): ?>
    <table>
      <thead>
        <tr>
          <th>No</th><th>NIP</th><th>Nama Guru</th>
          <?php if($metode==='topsis'): ?>
            <th>Skor TOPSIS</th><th>Peringkat TOPSIS</th>
          <?php else: ?>
            <th>Skor SAW</th><th>Peringkat SAW</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1; foreach($hasil as $h): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($h['nip_guru'] ?? '') ?></td>
          <td><?= htmlspecialchars($h['nama_guru'] ?? '') ?></td>
          <?php if($metode==='topsis'): ?>
            <td><?= number_format((float)($h['skor_topsis'] ?? 0),4) ?></td>
            <td><?= htmlspecialchars($h['peringkat_topsis'] ?? '-') ?></td>
          <?php else: ?>
            <td><?= number_format((float)($h['skor_saw'] ?? 0),4) ?></td>
            <td><?= htmlspecialchars($h['peringkat_saw'] ?? '-') ?></td>
          <?php endif; ?>
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
