<?php
$title = 'Data Perhitungan';
$rows = $penilaian ?? [];
$selectedMetode = $metode ?? '';
$guruMap = $guruMap ?? [];
$guruInfo = $guruInfo ?? [];
$critInfo = $sawDetail['bobot'] ?? ($topsisDetail['bobot'] ?? []);
$resolveGuru = function($gid) use ($guruInfo, $guruMap) {
  $nip = trim($guruInfo[$gid]['nip'] ?? '');
  $nama = $guruInfo[$gid]['nama'] ?? ($guruMap[$gid] ?? $gid);
  if ($nip === '' && is_string($nama) && strpos($nama, '-') !== false) {
    [$splitNip, $splitNama] = array_map('trim', explode('-', $nama, 2) + ['', '']);
    if ($splitNip !== '' && $splitNama !== '') {
      $nip = $splitNip;
      $nama = $splitNama;
    }
  }
  return [$nip, $nama];
};
ob_start();
?>
<form class="row g-3 align-items-end mb-3" method="post" action="/perhitungan/proses">
  <div class="col-md-6">
    <label class="form-label">Pilih Penilaian</label>
    <select name="id_penilaian" class="form-select" required>
      <option value="">-- Pilih Penilaian --</option>
      <?php foreach($rows as $p): ?>
        <option value="<?= $p['id_penilaian'] ?>" <?= ($p['id_penilaian']==$selected && $selectedMetode)?'selected':'' ?>>
          <?= htmlspecialchars($p['periode'] ?? '-') ?> - <?= htmlspecialchars($p['nip_penilai'] ?? '-') ?><?= !empty($p['nama_penilai']) ? ' / '.htmlspecialchars($p['nama_penilai']) : '' ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Metode</label>
    <select name="metode" class="form-select">
      <option value="">-- Pilih Metode --</option>
      <option value="saw" <?= $selectedMetode==='saw'?'selected':'' ?>>SAW</option>
      <option value="topsis" <?= $selectedMetode==='topsis'?'selected':'' ?>>TOPSIS</option>
    </select>
  </div>
  <div class="col-md-3">
    <button class="btn btn-primary" type="submit"><i class="bi bi-cpu me-1"></i>Proses</button>
  </div>
</form>

<?php if($selected && $selectedMetode && $hasil): ?>
  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead class="table-light">
        <tr>
          <th>No</th><th>NIP</th><th>Nama Guru</th>
          <?php if($selectedMetode==='saw'): ?>
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
          <td><?= htmlspecialchars($h['nip_guru'] ?? '-') ?></td>
          <td><?= htmlspecialchars($h['nama_guru'] ?? '-') ?></td>
          <?php if($selectedMetode==='saw'): ?>
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
  <?php if(!empty($_GET['err']) && $_GET['err']==='archive_all'): ?>
    <div class="alert alert-warning">Ada perhitungan yang masih published. Archive semua metode dulu sebelum menghitung ulang.</div>
  <?php elseif(!empty($_GET['err']) && $_GET['err']==='must_archive_other'): ?>
    <div class="alert alert-warning">Hanya satu metode boleh published. Archive metode lainnya terlebih dahulu.</div>
  <?php else: ?>
    <div class="alert alert-info">Belum ada hasil. Pilih metode lalu proses.</div>
  <?php endif; ?>
<?php endif; ?>

<?php if($rows): ?>
  <div class="card p-3 my-3">
    <h6 class="fw-bold mb-2">Riwayat Perhitungan</h6>
    <div class="accordion" id="historyAccordion">
      <?php $hidx=0; foreach($history ?? [] as $pid=>$hdata): ?>
      <?php $pen = $hdata['penilaian'] ?? []; $saw = $hdata['saw'] ?? null; $top = $hdata['topsis'] ?? null; ?>
      <div class="accordion-item">
        <h2 class="accordion-header" id="histHead<?= $hidx ?>">
          <button class="accordion-button <?= $hidx>0?'collapsed':'' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#histBody<?= $hidx ?>" aria-expanded="<?= $hidx===0?'true':'false' ?>">
            <?= htmlspecialchars($pen['periode'] ?? 'Periode') ?> - <?= htmlspecialchars($pen['nip_penilai'] ?? '-') ?><?= !empty($pen['nama_penilai']) ? ' / '.htmlspecialchars($pen['nama_penilai']) : '' ?>
          </button>
        </h2>
        <div id="histBody<?= $hidx ?>" class="accordion-collapse collapse <?= $hidx===0?'show':'' ?>" data-bs-parent="#historyAccordion">
          <div class="accordion-body">
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                  <tr><th>No</th><th>Metode</th><th>Status</th><th>Terakhir Dihitung</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                  <?php $no = 1; foreach(['saw'=>$saw,'topsis'=>$top] as $met => $row): ?>
                  <?php $status = $row['status'] ?? '-'; $created = $row['created_at'] ?? '-'; ?>
                  <tr>
                    <td><?= $no++ ?></td>
                    <td class="text-uppercase"><?= $met==='saw'?'SAW':'TOPSIS' ?></td>
                    <td><span class="badge bg-<?= $status==='archived' ? 'secondary' : 'success' ?>"><?= htmlspecialchars($status) ?></span></td>
                    <td><?= htmlspecialchars($created) ?></td>
                    <td class="d-flex gap-2">
                      <form method="post" action="/perhitungan/status" class="m-0">
                        <input type="hidden" name="id_penilaian" value="<?= $pid ?>">
                        <input type="hidden" name="metode" value="<?= $met ?>">
                        <input type="hidden" name="action" value="<?= $status==='published' ? 'archive' : 'publish' ?>">
                        <button class="btn btn-sm btn-outline-<?= $status==='published'?'secondary':'success' ?>" type="submit">
                          <i class="bi <?= $status==='published'?'bi-archive':'bi-upload' ?> me-1"></i><?= $status==='published' ? 'Archive' : 'Publish' ?>
                        </button>
                      </form>
                      <a class="btn btn-sm btn-outline-primary" href="/perhitungan?id_penilaian=<?= $pid ?>&metode=<?= $met ?>" target="_blank"><i class="bi bi-eye me-1"></i>Lihat Perhitungan</a>
                      <?php if($status==='published'): ?>
                        <a class="btn btn-sm btn-outline-info" href="/perhitungan/cetak?id_penilaian=<?= $pid ?>&metode=<?= $met ?>" target="_blank"><i class="bi bi-printer me-1"></i>Cetak</a>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <?php $hidx++; endforeach; ?>
      <?php if(empty($history)): ?>
      <div class="accordion-item">
        <div class="accordion-header">
          <div class="accordion-button collapsed">Belum ada riwayat</div>
        </div>
      </div>
      <?php endif; ?>
    </div>
    <div class="small text-muted mt-2">Catatan: satu penilaian hanya boleh punya satu metode berstatus published.</div>
  </div>
<?php endif; ?>

<?php if($selected && $selectedMetode==='saw' && !empty($sawDetail)): ?>
  <h6 class="mt-4">SAW - Matriks Keputusan (X)</h6>
  <div class="table-responsive mb-2">
      <table class="table table-sm table-bordered">
        <thead class="table-light"><tr><th>No</th><th>NIP</th><th>Nama Guru</th><?php foreach(array_keys($sawDetail['matrix'][array_key_first($sawDetail['matrix'])] ?? []) as $kid): ?><th><?= htmlspecialchars($critInfo[$kid]['nama'] ?? 'K'.$kid) ?></th><?php endforeach; ?></tr></thead>
      <tbody>
        <?php $no = 1; foreach($sawDetail['matrix'] as $gid=>$vals): ?>
          <tr>
            <?php [$nip,$nama] = $resolveGuru($gid); ?>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($nip) ?></td>
            <td><?= htmlspecialchars($nama) ?></td>
            <?php foreach($vals as $kid=>$v): ?><td><?= number_format((float)$v,4) ?></td><?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <h6>Normalisasi (R)</h6>
  <div class="table-responsive mb-2">
      <table class="table table-sm table-bordered">
        <thead class="table-light"><tr><th>No</th><th>NIP</th><th>Nama Guru</th><?php foreach(array_keys($sawDetail['norm'][array_key_first($sawDetail['norm'])] ?? []) as $kid): ?><th><?= htmlspecialchars($critInfo[$kid]['nama'] ?? 'K'.$kid) ?></th><?php endforeach; ?></tr></thead>
      <tbody>
        <?php $no = 1; foreach($sawDetail['norm'] as $gid=>$vals): ?>
          <tr>
            <?php [$nip,$nama] = $resolveGuru($gid); ?>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($nip) ?></td>
            <td><?= htmlspecialchars($nama) ?></td>
            <?php foreach($vals as $kid=>$v): ?><td><?= number_format((float)$v,4) ?></td><?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <h6>Normalisasi Terbobot (W×R)</h6>
  <div class="table-responsive mb-2">
      <table class="table table-sm table-bordered">
        <thead class="table-light"><tr><th>No</th><th>NIP</th><th>Nama Guru</th><?php foreach(array_keys($sawDetail['weighted'][array_key_first($sawDetail['weighted'])] ?? []) as $kid): ?><th><?= htmlspecialchars($critInfo[$kid]['nama'] ?? 'K'.$kid) ?></th><?php endforeach; ?></tr></thead>
      <tbody>
        <?php $no = 1; foreach($sawDetail['weighted'] as $gid=>$vals): ?>
          <tr>
            <?php [$nip,$nama] = $resolveGuru($gid); ?>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($nip) ?></td>
            <td><?= htmlspecialchars($nama) ?></td>
            <?php foreach($vals as $kid=>$v): ?><td><?= number_format((float)$v,4) ?></td><?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <h6>Skor Preferensi (V)</h6>
  <div class="table-responsive mb-2">
      <table class="table table-sm table-bordered">
        <thead class="table-light"><tr><th>No</th><th>NIP</th><th>Nama Guru</th><th>V</th></tr></thead>
      <tbody>
        <?php $no = 1; foreach($sawDetail['score'] as $gid=>$v): ?>
        <tr>
          <?php [$nip,$nama] = $resolveGuru($gid); ?>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($nip) ?></td>
          <td><?= htmlspecialchars($nama) ?></td>
          <td><?= number_format((float)$v,4) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php if($selected && $selectedMetode==='topsis' && !empty($topsisDetail)): ?>
  <h6 class="mt-4">TOPSIS - Matriks Keputusan (X)</h6>
  <div class="table-responsive mb-2">
      <table class="table table-sm table-bordered">
        <thead class="table-light"><tr><th>No</th><th>NIP</th><th>Nama Guru</th><?php foreach(array_keys($topsisDetail['matrix'][array_key_first($topsisDetail['matrix'])] ?? []) as $kid): ?><th><?= htmlspecialchars($critInfo[$kid]['nama'] ?? 'K'.$kid) ?></th><?php endforeach; ?></tr></thead>
      <tbody>
        <?php $no = 1; foreach($topsisDetail['matrix'] as $gid=>$vals): ?>
        <tr>
          <?php [$nip,$nama] = $resolveGuru($gid); ?>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($nip) ?></td>
          <td><?= htmlspecialchars($nama) ?></td>
          <?php foreach($vals as $kid=>$v): ?><td><?= number_format((float)$v,4) ?></td><?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <h6>TOPSIS - Normalisasi Vektor (R)</h6>
  <div class="table-responsive mb-2">
      <table class="table table-sm table-bordered">
        <thead class="table-light"><tr><th>No</th><th>NIP</th><th>Nama Guru</th><?php foreach(array_keys($topsisDetail['norm'][array_key_first($topsisDetail['norm'])] ?? []) as $kid): ?><th><?= htmlspecialchars($critInfo[$kid]['nama'] ?? 'K'.$kid) ?></th><?php endforeach; ?></tr></thead>
      <tbody>
        <?php $no = 1; foreach($topsisDetail['norm'] as $gid=>$vals): ?>
        <tr>
          <?php [$nip,$nama] = $resolveGuru($gid); ?>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($nip) ?></td>
          <td><?= htmlspecialchars($nama) ?></td>
          <?php foreach($vals as $kid=>$v): ?><td><?= number_format((float)$v,4) ?></td><?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <h6>TOPSIS - Normalisasi Terbobot (Y)</h6>
  <div class="table-responsive mb-2">
      <table class="table table-sm table-bordered">
        <thead class="table-light"><tr><th>No</th><th>NIP</th><th>Nama Guru</th><?php foreach(array_keys($topsisDetail['norm_weighted'][array_key_first($topsisDetail['norm_weighted'])] ?? []) as $kid): ?><th><?= htmlspecialchars($critInfo[$kid]['nama'] ?? 'K'.$kid) ?></th><?php endforeach; ?></tr></thead>
      <tbody>
        <?php $no = 1; foreach($topsisDetail['norm_weighted'] as $gid=>$vals): ?>
        <tr>
          <?php [$nip,$nama] = $resolveGuru($gid); ?>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($nip) ?></td>
          <td><?= htmlspecialchars($nama) ?></td>
          <?php foreach($vals as $kid=>$v): ?><td><?= number_format((float)$v,4) ?></td><?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <h6>TOPSIS - Solusi Ideal (+) dan (-)</h6>
  <div class="table-responsive mb-2">
    <table class="table table-sm table-bordered">
      <thead class="table-light"><tr><th>No</th><th></th><?php foreach($topsisDetail['ideal_plus'] as $kid=>$v): ?><th><?= htmlspecialchars($critInfo[$kid]['nama'] ?? 'K'.$kid) ?></th><?php endforeach; ?></tr></thead>
      <tbody>
        <tr><td>1</td><td>Ideal +</td><?php foreach($topsisDetail['ideal_plus'] as $v): ?><td><?= number_format((float)$v,4) ?></td><?php endforeach; ?></tr>
        <tr><td>2</td><td>Ideal -</td><?php foreach($topsisDetail['ideal_min'] as $v): ?><td><?= number_format((float)$v,4) ?></td><?php endforeach; ?></tr>
      </tbody>
    </table>
  </div>
  <h6>TOPSIS - Jarak dan Preferensi</h6>
  <div class="table-responsive mb-2">
      <table class="table table-sm table-bordered">
        <thead class="table-light"><tr><th>No</th><th>NIP</th><th>Nama Guru</th><th>D+</th><th>D-</th><th>Preferensi (Ci*)</th></tr></thead>
      <tbody>
        <?php $no = 1; foreach($topsisDetail['score'] as $gid=>$v): ?>
        <tr>
          <?php [$nip,$nama] = $resolveGuru($gid); ?>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($nip) ?></td>
          <td><?= htmlspecialchars($nama) ?></td>
          <td><?= number_format((float)($v['d_plus'] ?? 0),4) ?></td>
          <td><?= number_format((float)($v['d_min'] ?? 0),4) ?></td>
          <td><?= number_format((float)($v['pref'] ?? 0),4) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/main.php'; ?>
