<?php
$title = 'Data Guru';
ob_start();
$rows = $data['data'] ?? $data ?? [];
if (!is_array($rows)) $rows = [];
?>
<div class="d-flex justify-content-end align-items-center mb-3">
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddGuru"><i class="bi bi-plus-lg me-1"></i>Tambah</button>
</div>
<div class="table-responsive">
  <table class="table table-sm align-middle">
    <thead class="table-light">
      <tr>
        <th>No</th><th>NIP</th><th>Nama</th><th>Jenis Kelamin</th><th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php $no = 1; foreach($rows as $row): ?>
      <?php
        $id = $row['id_guru'] ?? $row['id'] ?? '';
        $nama = trim((string)($row['nama_guru'] ?? ''));
        $nip = trim((string)($row['nip_guru'] ?? ''));
        $jk = $row['jenis_kelamin'] ?? '';
        $display = fn($v) => $v === '' ? '-' : htmlspecialchars($v);
      ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= $display($nip) ?></td>
        <td><?= $display($nama) ?></td>
        <td><?= $jk === 'L' ? 'Laki-laki' : ($jk === 'P' ? 'Perempuan' : '-') ?></td>
        <td>
          <button class="btn btn-outline-secondary btn-sm btn-edit-guru"
                  data-id="<?= $id ?>"
                  data-nama="<?= htmlspecialchars($nama, ENT_QUOTES) ?>"
                  data-nip="<?= htmlspecialchars($nip, ENT_QUOTES) ?>"
                  data-jk="<?= htmlspecialchars($jk, ENT_QUOTES) ?>">
            <i class="bi bi-pencil-square me-1"></i>Edit
          </button>
          <button class="btn btn-outline-danger btn-sm btn-delete-guru"
                  data-id="<?= $id ?>"
                  data-nama="<?= htmlspecialchars($nama, ENT_QUOTES) ?>">
            <i class="bi bi-trash me-1"></i>Hapus
          </button>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?>
      <tr><td colspan="5" class="text-center text-muted">Belum ada data</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalAddGuru" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="/guru/store">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Guru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">NIP <span class="text-danger">*</span></label>
          <input type="text" name="nip_guru" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Nama Guru <span class="text-danger">*</span></label>
          <input type="text" name="nama_guru" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
          <select name="jenis_kelamin" class="form-select" required>
            <option value="">-- Pilih --</option>
            <option value="L">Laki-laki</option>
            <option value="P">Perempuan</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEditGuru" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="/guru/update">
      <div class="modal-header">
        <h5 class="modal-title">Edit Guru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="editIdGuru">
        <div class="mb-3">
          <label class="form-label">NIP <span class="text-danger">*</span></label>
          <input type="text" name="nip_guru" class="form-control" id="editNip" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Nama Guru <span class="text-danger">*</span></label>
          <input type="text" name="nama_guru" class="form-control" id="editNama" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
          <select name="jenis_kelamin" class="form-select" id="editJk" required>
            <option value="">-- Pilih --</option>
            <option value="L">Laki-laki</option>
            <option value="P">Perempuan</option>
          </select>
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
<div class="modal fade" id="modalDeleteGuru" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" method="post" action="/guru/delete">
      <div class="modal-header">
        <h5 class="modal-title text-danger">Hapus Guru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="deleteGuruId">
        <p class="mb-1">Yakin hapus guru berikut?</p>
        <div class="fw-semibold" id="deleteGuruNama"></div>
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
  const editModalEl = document.getElementById('modalEditGuru');
  const deleteModalEl = document.getElementById('modalDeleteGuru');
  const modalEdit = editModalEl ? new bootstrap.Modal(editModalEl) : null;
  const modalDelete = deleteModalEl ? new bootstrap.Modal(deleteModalEl) : null;
  document.querySelectorAll('.btn-edit-guru').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('editIdGuru').value = btn.dataset.id || '';
      document.getElementById('editNama').value = btn.dataset.nama || '';
      document.getElementById('editNip').value = btn.dataset.nip || '';
      document.getElementById('editJk').value = btn.dataset.jk || '';
      if (modalEdit) modalEdit.show();
    });
  });
  document.querySelectorAll('.btn-delete-guru').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('deleteGuruId').value = btn.dataset.id || '';
      document.getElementById('deleteGuruNama').textContent = btn.dataset.nama || '';
      if (modalDelete) modalDelete.show();
    });
  });
});
</script>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/main.php'; ?>
