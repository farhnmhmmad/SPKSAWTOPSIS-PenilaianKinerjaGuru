<?php
$title = 'Laporan';
$rows = $penilaian ?? [];
$metodeSelected = $_GET['metode'] ?? '';
$radar = $radar ?? [];
$trend = $trend ?? [];
$guruMap = $guruMap ?? [];
$kriteriaList = $kriteria ?? [];
ob_start();
?>
<form class="row g-3 align-items-end mb-3" method="get" action="/laporan">
  <div class="col-md-6">
    <label class="form-label">Penilaian</label>
    <select name="id_penilaian" class="form-select">
      <?php foreach($rows as $p): ?>
        <option value="<?= $p['id_penilaian'] ?>" <?= ($p['id_penilaian']==$selected)?'selected':'' ?>><?= htmlspecialchars($p['nama_penilaian']) ?> (<?= htmlspecialchars($p['tanggal_penilaian']) ?>)</option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Metode</label>
    <select name="metode" class="form-select">
      <option value="">-- Pilih Metode --</option>
      <option value="saw" <?= $metode==='saw'?'selected':'' ?>>SAW</option>
      <option value="topsis" <?= $metode==='topsis'?'selected':'' ?>>TOPSIS</option>
    </select>
  </div>
  <div class="col-md-2">
    <button class="btn btn-primary" type="submit">Tampilkan</button>
  </div>
</form>
<?php if($selected && $metode && $detail): ?>
  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card p-3 h-100">
        <h6 class="fw-bold mb-2"><?= $metode==='topsis' ? 'Skor TOPSIS' : 'Skor SAW' ?></h6>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead class="table-light">
              <tr><th>Guru</th><th>Skor</th><th>Rank</th></tr>
            </thead>
            <tbody>
              <?php foreach($detail as $d): ?>
              <tr>
                <td><?= htmlspecialchars($d['nama_guru']) ?></td>
                <td><?= number_format((float)($d['skor'] ?? 0),4) ?></td>
                <td><?= htmlspecialchars($d['rank'] ?? '-') ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="card p-3 h-100">
        <h6 class="fw-bold mb-2">Profil Kompetensi per Kriteria</h6>
        <?php if(!empty($radar)): ?>
          <div class="accordion" id="radarAccordion">
            <?php $ridx=0; foreach($radar as $gid=>$r): ?>
            <div class="accordion-item">
              <h2 class="accordion-header" id="radarHead<?= $ridx ?>">
                <button class="accordion-button <?= $ridx>0?'collapsed':'' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#radarBody<?= $ridx ?>" aria-expanded="<?= $ridx===0?'true':'false' ?>">
                  <?= htmlspecialchars($r['label'] ?? ($guruMap[$gid] ?? 'Guru')) ?>
                </button>
              </h2>
              <div id="radarBody<?= $ridx ?>" class="accordion-collapse collapse <?= $ridx===0?'show':'' ?>" data-bs-parent="#radarAccordion">
                <div class="accordion-body p-2">
                  <?php foreach($kriteriaList as $k): ?>
                    <?php $val = $r['values'][$k['nama_kriteria']] ?? 0; ?>
                    <?php
                      $barClass = 'bg-danger';
                      if ($val > 75) $barClass = 'bg-success';
                      elseif ($val > 50) $barClass = 'bg-warning';
                    ?>
                    <div class="mb-2">
                      <div class="d-flex justify-content-between">
                        <span class="small fw-semibold"><?= htmlspecialchars($k['nama_kriteria']) ?></span>
                        <span class="small"><?= number_format((float)$val,2) ?>%</span>
                      </div>
                      <div class="progress" style="height:6px;">
                        <div class="progress-bar <?= $barClass ?>" role="progressbar" style="width: <?= max(0,min(100,$val)) ?>%;"></div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
            <?php $ridx++; endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-muted small">Belum ada data kompetensi.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="card p-3 mt-3">
    <h6 class="fw-bold mb-2">Perkembangan Antar Periode</h6>
    <?php if($trend): ?>
      <?php
        $groupedTrend = [];
        foreach($trend as $t){
          $gid = $t['id_guru'] ?? 0;
          $groupedTrend[$gid]['nama'] = $t['nama_guru'] ?? 'Guru';
          $groupedTrend[$gid]['items'][] = $t;
        }
      ?>
      <div class="accordion" id="trendAccordion">
        <?php $tidx=0; foreach($groupedTrend as $gid=>$gdata): ?>
        <div class="accordion-item">
          <h2 class="accordion-header" id="trendHead<?= $tidx ?>">
            <button class="accordion-button <?= $tidx>0?'collapsed':'' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#trendBody<?= $tidx ?>" aria-expanded="<?= $tidx===0?'true':'false' ?>">
              <?= htmlspecialchars($gdata['nama']) ?>
            </button>
          </h2>
          <div id="trendBody<?= $tidx ?>" class="accordion-collapse collapse <?= $tidx===0?'show':'' ?>" data-bs-parent="#trendAccordion">
            <div class="accordion-body p-2">
              <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                  <thead class="table-light">
                    <tr><th>Penilaian</th><th>Tanggal</th><th>Skor <?= $metode==='topsis'?'TOPSIS':'SAW' ?></th><th>Rank</th></tr>
                  </thead>
                  <tbody>
                    <?php foreach($gdata['items'] as $item): ?>
                    <tr>
                      <td><?= htmlspecialchars($item['nama_penilaian'] ?? '') ?></td>
                      <td><?= htmlspecialchars($item['tanggal_penilaian'] ?? '') ?></td>
                      <td><?= number_format((float)($item['skor'] ?? 0),4) ?></td>
                      <td><?= htmlspecialchars($item['rank'] ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <?php $tidx++; endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center text-muted">Belum ada data trend.</div>
    <?php endif; ?>
  </div>
<?php elseif($selected): ?>
  <div class="alert alert-info">Pilih metode atau belum ada data.</div>
<?php endif; ?>

<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/main.php'; ?>
