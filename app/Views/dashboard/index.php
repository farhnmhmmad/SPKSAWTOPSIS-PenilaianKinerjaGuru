<?php $title = 'Dashboard'; ob_start(); ?>
<div class="mb-4">
  <h5 class="fw-bold mb-1">Selamat datang, <?= htmlspecialchars($user['username'] ?? '') ?></h5>
  <div class="text-muted">
    Ringkasan visual perhitungan kinerja guru untuk periode terpilih.
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="border rounded-3 p-3 bg-white h-100">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="fw-semibold">Top 5 Guru Terbaik</div>
        <div class="d-flex align-items-center gap-2">
          <form method="get" class="d-flex align-items-center gap-2">
            <?php if(!empty($selectedGuruId)): ?>
              <input type="hidden" name="id_guru" value="<?= (int)$selectedGuruId ?>">
            <?php endif; ?>
            <label for="id_penilaian" class="small text-muted">Periode</label>
            <select id="id_penilaian" name="id_penilaian" class="form-select form-select-sm" onchange="this.form.submit()">
              <?php foreach(($penilaianList ?? []) as $p): ?>
                <option value="<?= (int)$p['id_penilaian'] ?>" <?= (int)$selectedPenilaianId === (int)$p['id_penilaian'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($p['periode'] ?? '') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </form>
          <?php if(!empty($top5Method)): ?>
            <span class="badge bg-primary-subtle text-primary text-uppercase"><?= htmlspecialchars($top5Method) ?></span>
          <?php endif; ?>
        </div>
      </div>
      <?php if(empty($top5Labels)): ?>
        <div class="text-muted small">Belum ada data perhitungan untuk periode ini.</div>
      <?php else: ?>
        <div class="chart-box">
          <canvas id="chartTop5"></canvas>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="border rounded-3 p-3 bg-white h-100">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="fw-semibold">Riwayat Kinerja Individu</div>
      </div>
      <form method="get" class="d-flex align-items-center gap-2 mb-2">
        <?php if(!empty($selectedPenilaianId)): ?>
          <input type="hidden" name="id_penilaian" value="<?= (int)$selectedPenilaianId ?>">
        <?php endif; ?>
        <label for="id_guru" class="small text-muted">Guru</label>
        <select id="id_guru" name="id_guru" class="form-select form-select-sm" onchange="this.form.submit()">
          <?php foreach(($guruList ?? []) as $g): ?>
            <option value="<?= (int)$g['id_guru'] ?>" <?= (int)$selectedGuruId === (int)$g['id_guru'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($g['nama_guru'] ?? '') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>
      <?php if(empty($historyLabels)): ?>
        <div class="text-muted small">Belum ada riwayat perhitungan untuk guru ini.</div>
      <?php else: ?>
        <div class="chart-box">
          <canvas id="chartHistory"></canvas>
        </div>
        <div class="small text-muted mt-2">Peringkat lebih kecil berarti lebih baik.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<style>
  .chart-box{height:260px;}
  .chart-box canvas{width:100% !important;height:100% !important;}
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
  const top5Labels = <?= json_encode($top5Labels ?? []) ?>;
  const top5Scores = <?= json_encode($top5Scores ?? []) ?>;
  if (top5Labels.length) {
    new Chart(document.getElementById('chartTop5'), {
      type: 'bar',
      data: {
        labels: top5Labels,
        datasets: [{
          label: 'Skor',
          data: top5Scores,
          backgroundColor: 'rgba(37, 99, 235, 0.65)',
          borderColor: 'rgba(37, 99, 235, 1)',
          borderWidth: 1,
          borderRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: { beginAtZero: true, grid: { color: 'rgba(148,163,184,0.2)' } },
          x: { grid: { display: false } }
        },
        plugins: { legend: { display: false } }
      }
    });
  }

  const historyLabels = <?= json_encode($historyLabels ?? []) ?>;
  const historySaw = <?= json_encode($historySaw ?? []) ?>;
  const historyTopsis = <?= json_encode($historyTopsis ?? []) ?>;
  const historyCombined = <?= json_encode($historyCombined ?? []) ?>;
  if (historyLabels.length) {
    const allRanks = [...historySaw, ...historyTopsis].filter(v => v !== null && v !== undefined);
    let minRank = allRanks.length ? Math.min(...allRanks) : 1;
    let maxRank = allRanks.length ? Math.max(...allRanks) : 1;
    if (minRank === maxRank) {
      minRank = Math.max(1, minRank - 1);
      maxRank = maxRank + 1;
    }
    const pad = Math.max(1, Math.ceil((maxRank - minRank) * 0.35));
    minRank = Math.max(1, minRank - pad);
    maxRank = maxRank + pad;
    new Chart(document.getElementById('chartHistory'), {
      type: 'line',
      data: {
        labels: historyLabels,
        datasets: [{
          label: '',
          data: historyCombined,
          fill: false,
          tension: 0.35,
          borderColor: 'rgba(148, 163, 184, 1)',
          pointBackgroundColor: 'rgba(148, 163, 184, 1)',
          pointRadius: 2
        },{
          label: 'SAW',
          data: historySaw,
          fill: false,
          tension: 0.35,
          borderColor: 'rgba(37, 99, 235, 1)',
          pointBackgroundColor: 'rgba(37, 99, 235, 1)',
          pointRadius: 3
        },{
          label: 'TOPSIS',
          data: historyTopsis,
          fill: false,
          tension: 0.35,
          borderColor: 'rgba(16, 185, 129, 1)',
          pointBackgroundColor: 'rgba(16, 185, 129, 1)',
          pointRadius: 3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: false,
            reverse: true,
            min: minRank,
            max: maxRank,
            ticks: {
              stepSize: 1,
              precision: 0,
              callback: (value) => Number.isInteger(value) ? value : ''
            },
            grid: { color: 'rgba(148,163,184,0.2)' }
          },
          x: { grid: { display: false } }
        },
        plugins: {
          legend: {
            display: true,
            position: 'bottom',
            labels: {
              filter: (item) => item.text !== ''
            }
          },
          tooltip: {
            enabled: true,
            filter: (item) => item.dataset.label !== ''
          }
        }
      }
    });
  }
</script>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/main.php'; ?>
