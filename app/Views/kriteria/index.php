<?php
$title = 'Data Kriteria';
$rows = $data['data'] ?? $data ?? [];
if (!is_array($rows)) $rows = [];
ob_start();
?>
<div class="d-flex justify-content-end align-items-center mb-3">
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddKriteria"><i class="bi bi-plus-lg me-1"></i>Tambah</button>
</div>
<div class="table-responsive">
  <table class="table table-sm align-middle">
    <thead class="table-light">
      <tr>
        <th>No</th><th>Kode Kriteria</th><th>Nama Kriteria</th><th>Bobot Kriteria</th><th>Tipe Kriteria</th><th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php $no = 1; foreach($rows as $row): ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= htmlspecialchars($row['kode_kriteria'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['nama_kriteria'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['bobot_kriteria'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['tipe_kriteria'] ?? '') ?></td>
        <td>
          <button class="btn btn-outline-secondary btn-sm btn-edit-kriteria"
                  data-id="<?= $row['id_kriteria'] ?? '' ?>"
                  data-kode="<?= htmlspecialchars($row['kode_kriteria'] ?? '', ENT_QUOTES) ?>"
                  data-nama="<?= htmlspecialchars($row['nama_kriteria'] ?? '', ENT_QUOTES) ?>"
                  data-bobot="<?= htmlspecialchars($row['bobot_kriteria'] ?? '', ENT_QUOTES) ?>"
                  data-tipe="<?= htmlspecialchars($row['tipe_kriteria'] ?? '', ENT_QUOTES) ?>">
            <i class="bi bi-pencil-square me-1"></i>Edit
          </button>
          <button class="btn btn-outline-danger btn-sm btn-delete-kriteria"
                  data-id="<?= $row['id_kriteria'] ?? '' ?>"
                  data-nama="<?= htmlspecialchars($row['nama_kriteria'] ?? '', ENT_QUOTES) ?>">
            <i class="bi bi-trash me-1"></i>Hapus
          </button>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if(!$rows): ?>
      <tr><td colspan="6" class="text-center text-muted">Belum ada data</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="modal fade" id="modalAddKriteria" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="/kriteria/store">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Kriteria</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Kode <span class="text-danger">*</span></label>
            <input type="text" name="kode" class="form-control" required>
          </div>
          <div class="col-md-8">
            <label class="form-label">Nama <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control" required>
          </div>
        </div>
        <div class="row g-3 mt-1">
          <div class="col-md-4">
            <label class="form-label">Bobot (0-1) <span class="text-danger">*</span></label>
            <input type="number" name="bobot" step="0.01" min="0" max="1" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Tipe <span class="text-danger">*</span></label>
            <select name="tipe" class="form-select" required>
              <option value="benefit">Benefit</option>
              <option value="cost">Cost</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="modalEditKriteria" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="/kriteria/update">
      <div class="modal-header">
        <h5 class="modal-title">Edit Kriteria</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="editIdKrit">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Kode <span class="text-danger">*</span></label>
            <input type="text" name="kode" class="form-control" id="editKode" required>
          </div>
          <div class="col-md-8">
            <label class="form-label">Nama <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control" id="editNama" required>
          </div>
        </div>
        <div class="row g-3 mt-1">
          <div class="col-md-4">
            <label class="form-label">Bobot (0-1) <span class="text-danger">*</span></label>
            <input type="number" name="bobot" step="0.01" min="0" max="1" class="form-control" id="editBobot" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Tipe <span class="text-danger">*</span></label>
            <select name="tipe" class="form-select" id="editTipe" required>
              <option value="benefit">Benefit</option>
              <option value="cost">Cost</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="modalDeleteKriteria" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" method="post" action="/kriteria/delete">
      <div class="modal-header">
        <h5 class="modal-title text-danger">Hapus Kriteria</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="deleteKritId">
        <p class="mb-1">Yakin hapus kriteria berikut?</p>
        <div class="fw-semibold" id="deleteKritNama"></div>
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
  const editModalEl = document.getElementById('modalEditKriteria');
  const deleteModalEl = document.getElementById('modalDeleteKriteria');
  const modalEdit = editModalEl ? new bootstrap.Modal(editModalEl) : null;
  const modalDelete = deleteModalEl ? new bootstrap.Modal(deleteModalEl) : null;
  document.querySelectorAll('.btn-edit-kriteria').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('editIdKrit').value = btn.dataset.id || '';
      document.getElementById('editKode').value = btn.dataset.kode || '';
      document.getElementById('editNama').value = btn.dataset.nama || '';
      document.getElementById('editBobot').value = btn.dataset.bobot || '';
      document.getElementById('editTipe').value = btn.dataset.tipe || 'benefit';
      if (modalEdit) modalEdit.show();
    });
  });
  document.querySelectorAll('.btn-delete-kriteria').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('deleteKritId').value = btn.dataset.id || '';
      document.getElementById('deleteKritNama').textContent = btn.dataset.nama || '';
      if (modalDelete) modalDelete.show();
    });
  });
});
</script>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/main.php'; ?>
