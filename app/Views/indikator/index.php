<?php
$title = 'Data Indikator';
$listKriteria = $kriteria ?? [];
$subGrouped = $subGrouped ?? [];
$indiGrouped = $indiGrouped ?? [];
ob_start();
?>
<?php if(!$listKriteria): ?>
  <div class="alert alert-info">Belum ada kriteria/subkriteria. Tambah terlebih dahulu.</div>
<?php endif; ?>
<div class="accordion" id="kriteriaAccordion">
  <?php foreach($listKriteria as $idx=>$k): ?>
  <div class="accordion-item mb-2 border rounded">
    <h2 class="accordion-header" id="heading<?= $idx ?>">
      <button class="accordion-button <?= $idx>0?'collapsed':'' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $idx ?>" aria-expanded="<?= $idx===0?'true':'false' ?>" aria-controls="collapse<?= $idx ?>">
        <div>
          <div class="fw-bold"><?= htmlspecialchars($k['nama_kriteria']) ?></div>
          <div class="text-muted small"><?= htmlspecialchars($k['kode_kriteria'] ?? '') ?></div>
        </div>
      </button>
    </h2>
    <div id="collapse<?= $idx ?>" class="accordion-collapse collapse <?= $idx===0?'show':'' ?>" aria-labelledby="heading<?= $idx ?>" data-bs-parent="#kriteriaAccordion">
      <div class="accordion-body p-2">
        <?php foreach($subGrouped[$k['id_kriteria']] ?? [] as $s): ?>
          <div class="p-3 border rounded mb-2 bg-light">
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
              <div class="fw-semibold"><?= htmlspecialchars($s['nama_subkriteria']) ?></div>
              <div class="d-flex gap-2">
                <a class="btn btn-outline-primary btn-sm" href="/indikator/create?id_subkriteria=<?= $s['id_subkriteria'] ?>"><i class="bi bi-plus-lg me-1"></i>Tambah Indikator</a>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table table-sm mb-0">
                <thead class="table-light">
                  <tr><th>No</th><th>Indikator</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                  <?php $no = 1; foreach($indiGrouped[$s['id_subkriteria']] ?? [] as $i): ?>
                    <tr>
                      <td><?= $no++ ?></td>
                      <td><?= htmlspecialchars($i['nama_indikator'] ?? '') ?></td>
                      <td class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm btn-edit-indi"
                                data-id="<?= $i['id_indikator'] ?>"
                                data-sub="<?= $s['id_subkriteria'] ?>"
                                data-text="<?= htmlspecialchars($i['nama_indikator'] ?? '', ENT_QUOTES) ?>">
                          Edit
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm btn-delete-indi"
                                data-id="<?= $i['id_indikator'] ?>"
                                data-text="<?= htmlspecialchars($i['nama_indikator'] ?? '', ENT_QUOTES) ?>">
                          Hapus
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if(empty($indiGrouped[$s['id_subkriteria']])): ?>
                    <tr><td colspan="3" class="text-center text-muted">Belum ada indikator</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php endforeach; ?>
        <?php if(empty($subGrouped[$k['id_kriteria']])): ?>
          <div class="p-2 text-muted">Belum ada subkriteria</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editIndiModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="/indikator/update">
      <div class="modal-header">
        <h5 class="modal-title">Edit Indikator</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="modalIndiId">
        <div class="mb-3">
          <label class="form-label">Subkriteria <span class="text-danger">*</span></label>
          <select class="form-select" name="id_subkriteria" id="modalSubkriteria" required>
            <option value="">-- Pilih Subkriteria --</option>
            <?php foreach($listKriteria as $k): ?>
              <?php foreach($subGrouped[$k['id_kriteria']] ?? [] as $s): ?>
                <option value="<?= $s['id_subkriteria'] ?>"><?= htmlspecialchars($k['nama_kriteria'] ?? '') ?> — <?= htmlspecialchars($s['nama_subkriteria'] ?? '') ?></option>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Teks Indikator <span class="text-danger">*</span></label>
          <textarea class="form-control" name="teks" id="modalTeks" rows="3" required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Delete -->
<div class="modal fade" id="deleteIndiModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" method="post" action="/indikator/delete">
      <div class="modal-header">
        <h5 class="modal-title text-danger">Hapus Indikator</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="modalDeleteId">
        <p class="mb-1">Yakin hapus indikator berikut?</p>
        <div class="fw-semibold" id="modalDeleteText"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-danger">Hapus</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const editModalEl = document.getElementById('editIndiModal');
  const deleteModalEl = document.getElementById('deleteIndiModal');
  const modalEdit = editModalEl ? new bootstrap.Modal(editModalEl) : null;
  const modalDelete = deleteModalEl ? new bootstrap.Modal(deleteModalEl) : null;
  document.querySelectorAll('.btn-edit-indi').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.id || '';
      const sub = btn.dataset.sub || '';
      const teks = btn.dataset.text || '';
      document.getElementById('modalIndiId').value = id;
      document.getElementById('modalSubkriteria').value = sub;
      document.getElementById('modalTeks').value = teks;
      if (modalEdit) modalEdit.show();
    });
  });
  document.querySelectorAll('.btn-delete-indi').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.id || '';
      const teks = btn.dataset.text || '';
      document.getElementById('modalDeleteId').value = id;
      document.getElementById('modalDeleteText').textContent = teks;
      if (modalDelete) modalDelete.show();
    });
  });
});
</script>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/main.php'; ?>
