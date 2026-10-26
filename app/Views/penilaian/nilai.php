<?php $title = 'Penilaian Guru'; ob_start(); ?>
<?php
  $allDetail = $allDetail ?? [];
  $valueLabels = [
    0 => '0 - Tidak Terpenuhi',
    1 => '1 - Terpenuhi Sebagian',
    2 => '2 - Seluruhnya Terpenuhi'
  ];
  $subNames = [];
  foreach ($sub as $s) { $subNames[$s['id_subkriteria']] = $s['nama_subkriteria']; }
  $kriteriaNames = [];
  foreach (($kriteria ?? []) as $k) { $kriteriaNames[$k['id_kriteria']] = $k['nama_kriteria'] ?? ('Kriteria '.$k['id_kriteria']); }
  $indikatorBySub = [];
  foreach ($indikator as $i) {
    $indikatorBySub[$i['id_subkriteria']][] = $i;
  }
  $allCatatanKriteria = $allCatatanKriteria ?? [];
  $detailData = [];
  foreach ($guru as $g) {
    $gid = $g['id_guru'];
    $groups = [];
    foreach ($sub as $s) {
      $items = [];
      foreach ($indikatorBySub[$s['id_subkriteria']] ?? [] as $i) {
        $valRaw = $allDetail[$gid][$i['id_indikator']] ?? null;
        $val = is_null($valRaw) ? null : (int)$valRaw;
        $items[] = [
          'indikator' => $i['nama_indikator'],
          'nilai' => $val,
          'label' => is_null($val) ? 'Belum dinilai' : ($valueLabels[$val] ?? ('Nilai '.$val)),
        ];
      }
      $groups[] = [
        'sub' => $subNames[$s['id_subkriteria']] ?? 'Subkriteria',
        'id_kriteria' => (int)$s['id_kriteria'],
        'items' => $items,
        'catatan' => $allCatatanKriteria[$gid][$s['id_kriteria']] ?? '',
      ];
    }
    $detailData[$gid] = [
      'nama' => $g['nama_guru'] ?? '',
      'nip' => $g['nip_guru'] ?? '',
      'groups' => $groups,
    ];
  }
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <div class="text-muted small">Periode</div>
    <h5 class="mb-0 fw-bold"><?= htmlspecialchars($penilaian['periode'] ?? '') ?></h5>
    <div class="small text-muted">Penilai: <?= htmlspecialchars($penilaian['nama_penilai'] ?? '-') ?><?= !empty($penilaian['nip_penilai']) ? ' | NIP Penilai: '.htmlspecialchars($penilaian['nip_penilai']) : '' ?></div>
  </div>
  <?php if(!empty($selectedGuru)): ?>
    <a href="/penilaian/nilai?id=<?= $penilaian['id_penilaian'] ?>" class="btn btn-light btn-sm">Kembali</a>
  <?php else: ?>
    <a href="/penilaian" class="btn btn-light btn-sm">Kembali</a>
  <?php endif; ?>
</div>

<?php if(!$selectedGuru): ?>
  <div class="table-responsive mb-3">
    <table class="table table-sm align-middle">
      <thead class="table-light">
        <tr><th>No</th><th>NIP Guru</th><th>Nama Guru</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        <?php $no = 1; foreach($guru as $g): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($g['nip_guru'] ?? '') ?></td>
          <td><?= htmlspecialchars($g['nama_guru'] ?? '') ?></td>
          <td>
            <a class="btn btn-outline-primary btn-sm" href="/penilaian/nilai?id=<?= $penilaian['id_penilaian'] ?>&id_guru=<?= $g['id_guru'] ?>"><i class="bi bi-pencil me-1"></i>Nilai</a>
            <button type="button" class="btn btn-outline-secondary btn-sm btn-detail-nilai" data-guru="<?= $g['id_guru'] ?>">
              <i class="bi bi-eye me-1"></i>Detail
            </button>
            <button class="btn btn-outline-danger btn-sm btn-kosongkan-nilai"
                    data-penilaian="<?= $penilaian['id_penilaian'] ?>"
                    data-guru="<?= $g['id_guru'] ?>"
                    data-nama="<?= htmlspecialchars($g['nama_guru'], ENT_QUOTES) ?>"
                    data-locked="<?= !empty($locked) ? '1' : '0' ?>">
              <i class="bi bi-eraser me-1"></i>Kosongkan Nilai
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php if($selectedGuru): ?>
  <form method="post" action="/penilaian/simpan-nilai">
    <input type="hidden" name="id_penilaian" value="<?= $penilaian['id_penilaian'] ?>">
    <input type="hidden" name="id_guru" value="<?= $selectedGuru ?>">
    <?php if(!empty($locked)): ?>
      <div class="alert alert-warning">Perhitungan sudah dipublish. Archive semua metode untuk mengedit nilai.</div>
    <?php endif; ?>
    <div class="mb-3">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" id="setAllNilai2" <?= !empty($locked)?'disabled':'' ?>>
        <label class="form-check-label fw-semibold" for="setAllNilai2">
          Pilih semua nilai 2 (Seluruhnya Terpenuhi)
        </label>
      </div>
    </div>
    <div class="border rounded-3 p-3 mb-3">
      <?php
        $guruItem = null;
        foreach($guru as $g){ if($g['id_guru']==$selectedGuru){ $guruItem=$g; break; } }
      ?>
      <div class="fw-bold mb-2"><?= htmlspecialchars(($guruItem['nip_guru'] ?? '') . ($guruItem ? ' - ' : '') . ($guruItem['nama_guru'] ?? 'Guru')) ?></div>

      <div class="accordion" id="kriteriaAccordion">
        <?php foreach(($kriteria ?? []) as $idx => $k): ?>
          <?php $kid = (int)$k['id_kriteria']; ?>
          <div class="accordion-item mb-2 border rounded">
            <h2 class="accordion-header" id="headingKriteria<?= $idx ?>">
              <button class="accordion-button <?= $idx>0?'collapsed':'' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKriteria<?= $idx ?>" aria-expanded="<?= $idx===0?'true':'false' ?>" aria-controls="collapseKriteria<?= $idx ?>">
                <div class="fw-semibold"><?= htmlspecialchars($kriteriaNames[$kid] ?? ('Kriteria '.$kid)) ?></div>
              </button>
            </h2>
            <div id="collapseKriteria<?= $idx ?>" class="accordion-collapse collapse <?= $idx===0?'show':'' ?>" aria-labelledby="headingKriteria<?= $idx ?>" data-bs-parent="#kriteriaAccordion">
              <div class="accordion-body p-2">
                <div class="table-responsive">
                  <table class="table table-sm align-middle mb-2">
                    <thead class="table-light">
                      <tr>
                        <th style="width:48px;">No</th>
                        <th style="width:220px;">Sub-Kriteria</th>
                        <th>Indikator</th>
                        <th style="width:420px;">Nilai</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php $no = 1; $hasRow = false; ?>
                      <?php foreach($sub as $s): if((int)$s['id_kriteria'] !== $kid) continue; ?>
                        <?php if(!empty($indikatorBySub[$s['id_subkriteria']])): ?>
                          <?php foreach($indikatorBySub[$s['id_subkriteria']] as $i): $hasRow = true; ?>
                            <?php $current = $detail[$i['id_indikator']] ?? ''; ?>
                            <tr>
                              <td><?= $no++ ?></td>
                              <td><?= htmlspecialchars($s['nama_subkriteria'] ?? '') ?></td>
                              <td>
                                <?= htmlspecialchars($i['nama_indikator']) ?>
                                <span class="text-danger">*</span>
                              </td>
                              <td>
                                <div class="d-flex flex-wrap gap-3">
                                  <?php for($v=0; $v<=2; $v++): ?>
                                    <div class="form-check form-check-inline mb-0">
                                      <input class="form-check-input" type="radio" name="nilai[<?= $i['id_indikator'] ?>]" id="indi<?= $i['id_indikator'] ?>_<?= $v ?>" value="<?= $v ?>" <?= ((string)$current === (string)$v) ? 'checked' : '' ?> required>
                                      <label class="form-check-label" for="indi<?= $i['id_indikator'] ?>_<?= $v ?>"><?= $valueLabels[$v] ?></label>
                                    </div>
                                  <?php endfor; ?>
                                </div>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      <?php endforeach; ?>
                      <?php if(!$hasRow): ?>
                        <tr><td colspan="4" class="text-muted fst-italic">Belum ada indikator</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
                <div class="mt-2">
                  <label class="form-label fw-semibold">
                    Catatan Bukti Penilaian - <?= htmlspecialchars($kriteriaNames[$kid] ?? ('Kriteria '.$kid)) ?> <span class="text-danger">*</span>
                  </label>
                  <?php $cat = $catatanKriteriaMap[$kid] ?? ''; ?>
                  <textarea class="form-control" name="catatan_kriteria[<?= $kid ?>]" rows="3" placeholder="Contoh: Berdasarkan rekapitulasi supervisi kelas (Januari s.d Juni) dan pemeriksaan dokumen RPP Semester Genap." required><?= htmlspecialchars($cat) ?></textarea>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-primary" type="submit" <?= !empty($locked)?'disabled':'' ?>>Simpan Nilai</button>
    </div>
  </form>
<?php endif; ?>

<!-- Modal Kosongkan Nilai -->
<div class="modal fade" id="modalKosongkanNilai" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="/penilaian/hapus-nilai">
      <div class="modal-header">
        <h5 class="modal-title text-danger">Kosongkan Nilai</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_penilaian" id="kosongPenilaianId">
        <input type="hidden" name="id_guru" id="kosongGuruId">
        <p class="mb-1">Kosongkan semua nilai indikator untuk guru:</p>
        <div class="fw-semibold" id="kosongGuruNama"></div>
        <div class="text-warning small mt-2" id="kosongWarn" style="display:none;">Perhitungan sudah dipublish. Archive semua metode untuk mengkosongkan nilai.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-danger" id="kosongSubmit">Kosongkan</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const modalEl = document.getElementById('modalKosongkanNilai');
  const modal = modalEl ? new bootstrap.Modal(modalEl) : null;
  document.querySelectorAll('.btn-kosongkan-nilai').forEach(btn => {
    btn.addEventListener('click', () => {
      const locked = btn.dataset.locked === '1';
      document.getElementById('kosongPenilaianId').value = btn.dataset.penilaian || '';
      document.getElementById('kosongGuruId').value = btn.dataset.guru || '';
      document.getElementById('kosongGuruNama').textContent = btn.dataset.nama || '';
      const warn = document.getElementById('kosongWarn');
      const submit = document.getElementById('kosongSubmit');
      if (warn && submit) {
        warn.style.display = locked ? 'block' : 'none';
        submit.disabled = locked;
      }
      if (modal) modal.show();
    });
  });
  const detailData = <?= json_encode($detailData) ?>;
  const penilaianId = <?= (int)($penilaian['id_penilaian'] ?? 0) ?>;
  const detailModalEl = document.getElementById('modalDetailNilai');
  const detailModal = detailModalEl ? new bootstrap.Modal(detailModalEl) : null;
  const detailList = document.getElementById('detailList');
  const badgeForVal = (val) => {
    if (val === null || typeof val === 'undefined') return {cls:'secondary', text:'Belum dinilai'};
    if (val >= 2) return {cls:'success', text:'<?= $valueLabels[2] ?>'};
    if (val === 1) return {cls:'warning', text:'<?= $valueLabels[1] ?>'};
    return {cls:'danger', text:'<?= $valueLabels[0] ?>'};
  };
  document.querySelectorAll('.btn-detail-nilai').forEach(btn => {
    btn.addEventListener('click', () => {
      const gid = btn.dataset.guru;
      const data = detailData[gid];
      if (!data || !detailModal || !detailList) return;
      document.getElementById('detailGuruNama').textContent = data.nama || '';
      document.getElementById('detailGuruNip').textContent = data.nip || '-';
      detailList.innerHTML = '';
      const shownCatatanKriteria = new Set();
      (data.groups || []).forEach(group => {
        const header = document.createElement('div');
        header.className = 'list-group-item bg-light fw-semibold';
        header.textContent = group.sub || 'Subkriteria';
        detailList.appendChild(header);

        if (!group.items || group.items.length === 0) {
          const empty = document.createElement('div');
          empty.className = 'list-group-item text-muted fst-italic';
          empty.textContent = 'Belum ada indikator';
          detailList.appendChild(empty);
          return;
        }

        let idx = 1;
        group.items.forEach(item => {
          const badge = badgeForVal(item.nilai);
          const row = document.createElement('div');
          row.className = 'list-group-item d-flex justify-content-between align-items-start gap-2';

          const left = document.createElement('div');
          left.className = 'd-flex gap-2 align-items-start flex-grow-1';

          const number = document.createElement('span');
          number.className = 'fw-semibold text-muted';
          number.style.minWidth = '26px';
          number.textContent = idx + '.';
          idx++;

          const teks = document.createElement('div');
          teks.textContent = item.indikator || '';

          left.appendChild(number);
          left.appendChild(teks);
          row.appendChild(left);

          const badgeEl = document.createElement('span');
          badgeEl.className = 'badge bg-' + badge.cls;
          badgeEl.textContent = badge.text;
          row.appendChild(badgeEl);

          detailList.appendChild(row);
        });

        const kid = Number(group.id_kriteria || 0);
        if (!shownCatatanKriteria.has(kid)) {
          shownCatatanKriteria.add(kid);
          const catRow = document.createElement('div');
          catRow.className = 'list-group-item';
          const catLabel = document.createElement('div');
          catLabel.className = 'small text-muted';
          catLabel.textContent = 'Catatan Bukti Penilaian';
          const catText = document.createElement('div');
          catText.className = 'fw-semibold';
          catText.textContent = group.catatan ? group.catatan : '-';
          catRow.appendChild(catLabel);
          catRow.appendChild(catText);
          detailList.appendChild(catRow);
        }
      });
      const btnCetak = document.getElementById('btnCetakDetail');
      if (btnCetak) {
        btnCetak.href = `/penilaian/cetak-detail?id=${penilaianId}&id_guru=${gid}`;
      }
      detailModal.show();
    });
  });

  const form = document.querySelector('form[action="/penilaian/simpan-nilai"]');
  if (!form) return;

  const requiredMsg = 'Harap isi bidang ini.';
  const setAll = document.getElementById('setAllNilai2');
  const openAccordionFor = (el) => {
    if (!el) return;
    const item = el.closest('.accordion-item');
    const collapseEl = item ? item.querySelector('.accordion-collapse') : null;
    const btn = item ? item.querySelector('.accordion-button') : null;
    if (collapseEl && !collapseEl.classList.contains('show')) {
      if (window.bootstrap && bootstrap.Collapse) {
        const instance = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
        instance.show();
      } else {
        collapseEl.classList.add('show');
        if (btn) {
          btn.classList.remove('collapsed');
          btn.setAttribute('aria-expanded', 'true');
        }
      }
    }
    setTimeout(() => {
      if (typeof el.scrollIntoView === 'function') {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }, 350);
  };

  form.querySelectorAll('textarea[required], input[type="radio"]').forEach(el => {
    el.addEventListener('input', () => el.setCustomValidity(''));
    el.addEventListener('change', () => el.setCustomValidity(''));
  });

  form.setAttribute('novalidate', 'novalidate');

  if (setAll) {
    setAll.addEventListener('change', () => {
      if (!setAll.checked) return;
      form.querySelectorAll('input[type="radio"][value="2"]').forEach(r => {
        r.checked = true;
        r.setCustomValidity('');
      });
    });
  }

  form.addEventListener('submit', (e) => {
    let invalidEl = null;

    const items = Array.from(form.querySelectorAll('.accordion-item'));
    for (const item of items) {
      if (invalidEl) break;

      const radios = Array.from(item.querySelectorAll('input[type="radio"][name^="nilai["]'));
      const radioNames = new Set(radios.map(r => r.name));
      for (const name of radioNames) {
        const group = radios.filter(r => r.name === name);
        const checked = group.some(r => r.checked);
        if (!checked && group[0]) {
          group[0].setCustomValidity(requiredMsg);
          invalidEl = group[0];
          break;
        }
      }

      if (invalidEl) break;

      const ta = Array.from(item.querySelectorAll('textarea[required]'))
        .find(t => !t.value.trim());
      if (ta) {
        ta.setCustomValidity(requiredMsg);
        invalidEl = ta;
        break;
      }
    }

    if (invalidEl) {
      e.preventDefault();
      openAccordionFor(invalidEl);
      setTimeout(() => {
        invalidEl.reportValidity();
        invalidEl.focus({ preventScroll: false });
      }, 380);
      return;
    }
    form.submit();
  });
});
</script>

<!-- Modal Detail Nilai -->
<div class="modal fade" id="modalDetailNilai" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-0">Detail Penilaian</h5>
          <div class="small text-muted" id="detailGuruNama"></div>
          <div class="small" id="detailGuruNip"></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="list-group list-group-flush" id="detailList"></div>
        <div class="text-muted small mt-3">Gunakan untuk cross-check sebelum perhitungan.</div>
      </div>
      <div class="modal-footer">
        <a id="btnCetakDetail" class="btn btn-outline-primary" href="#" target="_blank">Cetak</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/main.php'; ?>
