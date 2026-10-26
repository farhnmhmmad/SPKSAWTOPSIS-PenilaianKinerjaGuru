<?php
$title = 'Data Peringkat';
$rows = $penilaian ?? [];
$hasil = $hasil ?? [];
$err = $err ?? '';
$selected = $selected ?? '';
$metode = $metode ?? '';
$requestedId = $_GET['id_penilaian'] ?? '';
if ($requestedId === '') {
  $selected = '';
  $hasil = [];
  $err = '';
  $metode = '';
}
ob_start();
?>
<form class="row g-3 align-items-end mb-3" method="get" action="/peringkat">
  <div class="col-md-6">
    <label class="form-label">Penilaian</label>
    <select name="id_penilaian" class="form-select">
      <option value="">-- Pilih Penilaian --</option>
      <?php foreach($rows as $p): ?>
        <?php
          $labelPeriode = htmlspecialchars($p['periode'] ?? '-');
          $labelNip = htmlspecialchars($p['nip_penilai'] ?? '-');
          $labelNama = $p['nama_penilai'] ?? '';
          $labelNama = $labelNama !== '' ? ' / '.htmlspecialchars($labelNama) : '';
          $isSelected = $requestedId !== '' && ($p['id_penilaian'] ?? '') == $requestedId;
        ?>
        <option value="<?= $p['id_penilaian'] ?>" <?= $isSelected ? 'selected' : '' ?>>
          <?= $labelPeriode ?> - <?= $labelNip ?><?= $labelNama ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-auto d-flex align-items-end gap-2 ms-md-2 mt-2 mt-md-0">
    <button class="btn btn-primary" type="submit"><i class="bi bi-eye me-1"></i>Tampilkan</button>
    <?php if($selected && $hasil && !$err): ?>
      <a class="btn btn-outline-primary" href="/peringkat/cetak?id_penilaian=<?= $selected ?>" target="_blank"><i class="bi bi-printer me-1"></i>Cetak</a>
    <?php endif; ?>
  </div>
</form>

<?php if($requestedId !== '' && $selected && $hasil && !$err): ?>
  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead class="table-light">
        <tr>
          <th>No</th><th>NIP</th><th>Nama Guru</th>
          <?php if($metode==='saw'): ?>
            <th>Skor SAW</th><th>Peringkat SAW</th>
          <?php else: ?>
            <th>Skor TOPSIS</th><th>Peringkat TOPSIS</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1; foreach($hasil as $h): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($h['nip_guru'] ?? '') ?></td>
          <td><?= htmlspecialchars($h['nama_guru'] ?? '') ?></td>
          <?php if($metode==='saw'): ?>
            <td><?= number_format((float)$h['skor_saw'], 4) ?></td>
            <td><?= htmlspecialchars($h['peringkat_saw']) ?></td>
          <?php else: ?>
            <td><?= number_format((float)$h['skor_topsis'], 4) ?></td>
            <td><?= htmlspecialchars($h['peringkat_topsis']) ?></td>
          <?php endif; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php elseif($selected): ?>
  <?php if($err==='publish_any'): ?>
    <div class="alert alert-warning">Belum ada metode yang dipublish untuk penilaian ini. Silakan publish perhitungan terlebih dahulu.</div>
  <?php else: ?>
    <div class="alert alert-info">Belum ada hasil. Pastikan perhitungan sudah dilakukan dan dipublish.</div>
  <?php endif; ?>
<?php endif; ?>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/main.php'; ?>
