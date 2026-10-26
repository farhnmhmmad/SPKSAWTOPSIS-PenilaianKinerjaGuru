<?php
$title = 'Cetak Perhitungan';
$met   = $metode ?? '';
$pen   = $penilaian ?? [];
$saw   = $sawDetail ?? [];
$top   = $topsisDetail ?? [];
$guruMap = $guruMap ?? [];
$guruInfo = $guruInfo ?? [];
$resolveGuru = function($gid) use ($guruInfo, $guruMap) {
  $nip = trim($guruInfo[$gid]['nip'] ?? '');
  $nama = $guruInfo[$gid]['nama'] ?? ($guruMap[$gid] ?? $gid);
  if ($nip === '' && is_string($nama) && strpos($nama, '-') !== false) {
    [$splitNip, $splitNama] = array_map('trim', explode('-', $nama, 2) + ['', '']);
    if ($splitNip !== '' && $splitNama !== '') { $nip = $splitNip; $nama = $splitNama; }
  }
  return [$nip, $nama];
};
$printedBy = $_SESSION['user']['username'] ?? 'Guest';
$printedAt = date('Y-m-d');
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
    .section{margin-bottom:14px;}
    .badge{display:inline-block;padding:4px 10px;border-radius:12px;background:#e5edff;color:#2d5bff;font-weight:700;font-size:12px;margin-left:8px;}
    thead{display:table-header-group;}
    tr{page-break-inside:avoid;}
    .compact th,.compact td{padding:6px;font-size:12px;}
    .mono th,.mono td{font-family:"Courier New",monospace;font-size:12px;}
    .page-break{page-break-after:always;}
    .footer{position:fixed;bottom:0;left:0;right:0;font-size:11px;color:#555;display:flex;justify-content:space-between;border-top:1px solid #ccc;padding-top:4px;}
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
  <?php
    $statusSaw = $pen['status_saw'] ?? ($pen['status'] ?? '');
    $statusTop = $pen['status_topsis'] ?? ($pen['status'] ?? '');
    $status = $met==='topsis' ? $statusTop : $statusSaw;
    $statusLabel = $status==='published' ? 'Published' : ($status==='archived' ? 'Archived' : 'Draft');
    $createdBy = $pen['created_by'] ?? ($pen['admin'] ?? $printedBy);
  ?>
  <div class="header-grid">
    <div class="logo"><img src="/uploads/logo-sekolah.png" alt="Logo" onerror="this.style.display='none'"></div>
    <div class="title-block">
      <h2>Cetak Perhitungan <?= strtoupper($met) ?></h2>
      <div class="meta-line">Periode: <?= htmlspecialchars($pen['periode'] ?? '-') ?> | Penilai: <?= htmlspecialchars($pen['nama_penilai'] ?? '-') ?><?= !empty($pen['nip_penilai']) ? ' / '.htmlspecialchars($pen['nip_penilai']) : '' ?> (<?= htmlspecialchars(substr($pen['tanggal_penilaian'] ?? '-',0,10)) ?>)</div>
      <div class="meta-line">Tanggal Cetak: <?= htmlspecialchars($printedAt) ?> &nbsp;|&nbsp; Dibuat oleh: <?= htmlspecialchars($createdBy) ?></div>
    </div>
    <div style="text-align:right;">
      <a class="btn-print no-print" href="#" onclick="window.print();return false;"><i class="bi bi-printer me-1"></i>Print / PDF</a>
    </div>
  </div>

  <?php if($met==='saw' && !empty($saw)): ?>
    <div class="section">
      <h4>01) Bobot Kriteria</h4>
      <table>
        <thead><tr><th>No</th><th>Kriteria</th><th>Bobot</th></tr></thead>
        <tbody>
          <?php $no = 1; foreach($saw['bobot'] as $kid=>$info): ?>
          <tr><td><?= $no++ ?></td><td><?= htmlspecialchars($info['nama'] ?? 'K'.$kid) ?></td><td><?= number_format((float)$info['bobot'],4) ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="section">
      <h4>02) Matriks Keputusan (X)</h4>
      <table class="compact">
        <thead><tr><th>No</th><th>NIP</th><th>Nama Guru</th><?php foreach(array_keys($saw['matrix'][array_key_first($saw['matrix'])] ?? []) as $kid): ?><th><?= htmlspecialchars($saw['bobot'][$kid]['nama'] ?? 'K'.$kid) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
          <?php $no = 1; foreach($saw['matrix'] as $gid=>$vals): ?>
            <?php [$nip,$nama] = $resolveGuru($gid); ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= htmlspecialchars($nip) ?></td>
              <td><?= htmlspecialchars($nama) ?></td>
              <?php foreach($vals as $kid=>$v): ?><td><?= number_format((float)$v,4) ?></td><?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="section">
      <h4>03) Normalisasi</h4>
      <table class="compact mono">
        <thead><tr><th>No</th><th>NIP</th><th>Nama Guru</th><?php foreach(array_keys($saw['norm'][array_key_first($saw['norm'])] ?? []) as $kid): ?><th><?= htmlspecialchars($saw['bobot'][$kid]['nama'] ?? 'K'.$kid) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
          <?php $no = 1; foreach($saw['norm'] as $gid=>$vals): ?>
            <?php [$nip,$nama] = $resolveGuru($gid); ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= htmlspecialchars($nip) ?></td>
              <td><?= htmlspecialchars($nama) ?></td>
              <?php foreach($vals as $kid=>$v): ?><td><?= number_format((float)$v,4) ?></td><?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="section">
      <h4>04) Normalisasi Terbobot (W×R)</h4>
      <table class="compact mono">
        <thead><tr><th>No</th><th>NIP</th><th>Nama Guru</th><?php foreach(array_keys($saw['weighted'][array_key_first($saw['weighted'])] ?? []) as $kid): ?><th><?= htmlspecialchars($saw['bobot'][$kid]['nama'] ?? 'K'.$kid) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
          <?php $no = 1; foreach($saw['weighted'] as $gid=>$vals): ?>
            <?php [$nip,$nama] = $resolveGuru($gid); ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= htmlspecialchars($nip) ?></td>
              <td><?= htmlspecialchars($nama) ?></td>
              <?php foreach($vals as $kid=>$v): ?><td><?= number_format((float)$v,4) ?></td><?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="section">
      <h4>05) Nilai Akhir SAW</h4>
      <table class="compact">
        <thead><tr><th>No</th><th>NIP</th><th>Guru</th><th>Skor</th><th>Peringkat SAW</th></tr></thead>
        <tbody>
          <?php $no = 1; foreach($saw['score'] as $gid=>$v): ?>
          <?php $info = $guruInfo[$gid] ?? ['nip'=>'','nama'=>$guruMap[$gid] ?? $gid]; ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($info['nip'] ?? '') ?></td>
            <td><?= htmlspecialchars($info['nama'] ?? ($guruMap[$gid] ?? $gid)) ?></td>
            <td><?= number_format((float)$v,4) ?></td>
            <td><?= htmlspecialchars($saw['peringkat'][$gid] ?? '-') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php elseif($met==='topsis' && !empty($top)): ?>
    <div class="section">
      <h4>01) Bobot Kriteria</h4>
      <table>
        <thead><tr><th>No</th><th>Kriteria</th><th>Bobot</th></tr></thead>
        <tbody>
          <?php $no = 1; foreach($top['bobot'] as $kid=>$info): ?>
          <tr><td><?= $no++ ?></td><td><?= htmlspecialchars($info['nama'] ?? 'K'.$kid) ?></td><td><?= number_format((float)$info['bobot'],4) ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="section">
      <h4>02) Matriks Keputusan (X)</h4>
      <table class="compact">
        <thead><tr><th>No</th><th>NIP</th><th>Nama Guru</th><?php foreach(array_keys($top['matrix'][array_key_first($top['matrix'])] ?? []) as $kid): ?><th><?= htmlspecialchars($top['bobot'][$kid]['nama'] ?? 'K'.$kid) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
          <?php $no = 1; foreach($top['matrix'] as $gid=>$vals): ?>
            <?php [$nip,$nama] = $resolveGuru($gid); ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= htmlspecialchars($nip) ?></td>
              <td><?= htmlspecialchars($nama) ?></td>
              <?php foreach($vals as $kid=>$v): ?><td><?= number_format((float)$v,4) ?></td><?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="section">
      <h4>03) Normalisasi Vektor (R)</h4>
      <table class="compact mono">
        <thead><tr><th>No</th><th>NIP</th><th>Nama Guru</th><?php foreach(array_keys($top['norm'][array_key_first($top['norm'])] ?? []) as $kid): ?><th><?= htmlspecialchars($top['bobot'][$kid]['nama'] ?? 'K'.$kid) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
          <?php $no = 1; foreach($top['norm'] as $gid=>$vals): ?>
            <?php [$nip,$nama] = $resolveGuru($gid); ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= htmlspecialchars($nip) ?></td>
              <td><?= htmlspecialchars($nama) ?></td>
              <?php foreach($vals as $kid=>$v): ?><td><?= number_format((float)$v,4) ?></td><?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="section">
      <h4>04) Normalisasi Terbobot (Y)</h4>
      <table class="compact mono">
        <thead><tr><th>No</th><th>NIP</th><th>Nama Guru</th><?php foreach(array_keys($top['norm_weighted'][array_key_first($top['norm_weighted'])] ?? []) as $kid): ?><th><?= htmlspecialchars($top['bobot'][$kid]['nama'] ?? 'K'.$kid) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
          <?php $no = 1; foreach($top['norm_weighted'] as $gid=>$vals): ?>
            <?php [$nip,$nama] = $resolveGuru($gid); ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= htmlspecialchars($nip) ?></td>
              <td><?= htmlspecialchars($nama) ?></td>
              <?php foreach($vals as $kid=>$v): ?><td><?= number_format((float)$v,4) ?></td><?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="section">
      <h4>05) Solusi Ideal (A+ dan A-)</h4>
      <table class="compact">
        <thead><tr><th>No</th><th></th><?php foreach($top['ideal_plus'] as $kid=>$v): ?><th><?= htmlspecialchars($top['bobot'][$kid]['nama'] ?? 'K'.$kid) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
          <tr><td>1</td><td>Ideal +</td><?php foreach($top['ideal_plus'] as $v): ?><td><?= number_format((float)$v,4) ?></td><?php endforeach; ?></tr>
          <tr><td>2</td><td>Ideal -</td><?php foreach($top['ideal_min'] as $v): ?><td><?= number_format((float)$v,4) ?></td><?php endforeach; ?></tr>
        </tbody>
      </table>
    </div>

    <div class="section">
      <h4>06) Jarak D+ dan D-</h4>
      <table class="compact">
        <thead><tr><th>No</th><th>NIP</th><th>Nama Guru</th><th>D+</th><th>D-</th></tr></thead>
        <tbody>
          <?php $no = 1; foreach($top['score'] as $gid=>$v): ?>
          <?php [$nip,$nama] = $resolveGuru($gid); ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($nip) ?></td>
            <td><?= htmlspecialchars($nama) ?></td>
            <td><?= number_format((float)($v['d_plus'] ?? 0),4) ?></td>
            <td><?= number_format((float)($v['d_min'] ?? 0),4) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="section">
      <h4>07) Nilai Akhir TOPSIS</h4>
      <table class="compact">
        <thead><tr><th>No</th><th>NIP</th><th>Guru</th><th>Preferensi (Ci*)</th><th>Peringkat TOPSIS</th></tr></thead>
        <tbody>
          <?php $no = 1; foreach($top['score'] as $gid=>$v): ?>
          <?php $info = $guruInfo[$gid] ?? ['nip'=>'','nama'=>$guruMap[$gid] ?? $gid]; ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($info['nip'] ?? '') ?></td>
            <td><?= htmlspecialchars($info['nama'] ?? ($guruMap[$gid] ?? $gid)) ?></td>
            <td><?= number_format((float)($v['pref'] ?? 0),4) ?></td>
            <td><?= htmlspecialchars($top['peringkat'][$gid] ?? '-') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="page-break"></div>
  <?php else: ?>
    <div style="padding:12px;border:1px solid #ddd;background:#f8fafc;">Belum ada data untuk dicetak.</div>
  <?php endif; ?>
</div>

</body>
</html>
