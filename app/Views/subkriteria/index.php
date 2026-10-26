<?php
$title = 'Data Subkriteria';
$listKriteria = $kriteria ?? [];
$grouped = $grouped ?? [];
ob_start();
?>
<?php if(!$listKriteria): ?>
  <div class="alert alert-info">Belum ada kriteria. Tambah kriteria terlebih dahulu.</div>
<?php endif; ?>
<div class="accordion" id="subAccordion">
  <?php foreach($listKriteria as $idx=>$k): ?>
  <div class="accordion-item mb-2 border rounded">
    <h2 class="accordion-header" id="headingSub<?= $idx ?>">
      <button class="accordion-button <?= $idx>0?'collapsed':'' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSub<?= $idx ?>" aria-expanded="<?= $idx===0?'true':'false' ?>" aria-controls="collapseSub<?= $idx ?>">
        <div>
          <div class="fw-bold"><?= htmlspecialchars($k['nama_kriteria']) ?></div>
          <div class="text-muted small"><?= htmlspecialchars($k['kode_kriteria'] ?? '') ?></div>
        </div>
      </button>
    </h2>
    <div id="collapseSub<?= $idx ?>" class="accordion-collapse collapse <?= $idx===0?'show':'' ?>" aria-labelledby="headingSub<?= $idx ?>" data-bs-parent="#subAccordion">
      <div class="accordion-body p-2">
        <div class="d-flex justify-content-end mb-2">
          <a class="btn btn-outline-primary btn-sm" href="/subkriteria/create?id_kriteria=<?= $k['id_kriteria'] ?>"><i class="bi bi-plus-lg me-1"></i>Tambah Subkriteria</a>
        </div>
        <table class="table table-sm mb-0">
          <thead class="table-light">
            <tr><th>No</th><th>Nama Subkriteria</th><th>Aksi</th></tr>
          </thead>
          <tbody>
            <?php $no = 1; foreach($grouped[$k['id_kriteria']] ?? [] as $s): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= htmlspecialchars($s['nama_subkriteria'] ?? '') ?></td>
              <td>
                <button class="btn btn-outline-secondary btn-sm btn-edit-sub"
                        data-id="<?= $s['id_subkriteria'] ?>"
                        data-kid="<?= $s['id_kriteria'] ?>"
                        data-nama="<?= htmlspecialchars($s['nama_subkriteria'] ?? '', ENT_QUOTES) ?>">
                  <i class="bi bi-pencil-square me-1"></i>Edit
                </button>
                <button class="btn btn-outline-danger btn-sm btn-delete-sub"
                        data-id="<?= $s['id_subkriteria'] ?>"
                        data-nama="<?= htmlspecialchars($s['nama_subkriteria'] ?? '', ENT_QUOTES) ?>">
                  <i class="bi bi-trash me-1"></i>Hapus
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($grouped[$k['id_kriteria']])): ?>
            <tr><td colspan="3" class="text-center text-muted">Belum ada subkriteria</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEditSub" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="/subkriteria/update">
      <div class="modal-header">
        <h5 class="modal-title">Edit Subkriteria</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="editSubId">
        <div class="mb-3">
          <label class="form-label">Kriteria <span class="text-danger">*</span></label>
          <select name="id_kriteria" class="form-select" id="editKid" required>
            <option value="">-- Pilih Kriteria --</option>
            <?php foreach($listKriteria as $k): ?>
              <option value="<?= $k['id_kriteria'] ?>"><?= htmlspecialchars($k['nama_kriteria'] ?? '') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Nama Subkriteria <span class="text-danger">*</span></label>
          <input type="text" name="nama_subkriteria" class="form-control" id="editNama" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Hapus -->
<div class="modal fade" id="modalDeleteSub" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" method="post" action="/subkriteria/delete">
      <div class="modal-header">
        <h5 class="modal-title text-danger">Hapus Subkriteria</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="deleteSubId">
        <p class="mb-1">Yakin hapus subkriteria berikut?</p>
        <div class="fw-semibold" id="deleteSubNama"></div>
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
  const editModalEl = document.getElementById('modalEditSub');
  const deleteModalEl = document.getElementById('modalDeleteSub');
  const modalEdit = editModalEl ? new bootstrap.Modal(editModalEl) : null;
  const modalDelete = deleteModalEl ? new bootstrap.Modal(deleteModalEl) : null;
  document.querySelectorAll('.btn-edit-sub').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('editSubId').value = btn.dataset.id || '';
      document.getElementById('editKid').value = btn.dataset.kid || '';
      document.getElementById('editNama').value = btn.dataset.nama || '';
      if (modalEdit) modalEdit.show();
    });
  });
  document.querySelectorAll('.btn-delete-sub').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('deleteSubId').value = btn.dataset.id || '';
      document.getElementById('deleteSubNama').textContent = btn.dataset.nama || '';
      if (modalDelete) modalDelete.show();
    });
  });
});
</script>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/main.php'; ?>
