<?php
$title = 'Kelola User';
$rows = $data['data'] ?? $data ?? [];
if (!is_array($rows)) $rows = [];
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
  </div>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddUser"><i class="bi bi-plus-lg me-1"></i>Tambah</button>
</div>
<div class="table-responsive">
  <table class="table table-sm align-middle">
    <thead class="table-light">
      <tr>
        <th>No</th><th>Username</th><th>Email</th><th>Role</th><th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php $no = 1; foreach($rows as $r): ?>
      <?php
        $id = $r['id'] ?? '';
        $username = $r['username'] ?? '';
        $email = $r['email'] ?? '';
        $role = $r['role'] ?? '';
      ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= htmlspecialchars($username) ?></td>
        <td><?= htmlspecialchars($email ?? '-') ?></td>
        <td><?= htmlspecialchars($role) ?></td>
        <td>
          <button class="btn btn-outline-secondary btn-sm btn-edit-user"
                  data-id="<?= $id ?>"
                  data-username="<?= htmlspecialchars($username, ENT_QUOTES) ?>"
                  data-email="<?= htmlspecialchars($email, ENT_QUOTES) ?>"
                  data-role="<?= htmlspecialchars($role, ENT_QUOTES) ?>">
            <i class="bi bi-pencil-square me-1"></i>Edit
          </button>
          <button class="btn btn-outline-danger btn-sm btn-delete-user"
                  data-id="<?= $id ?>"
                  data-username="<?= htmlspecialchars($username, ENT_QUOTES) ?>">
            <i class="bi bi-trash me-1"></i>Hapus
          </button>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if(!$rows): ?>
      <tr><td colspan="5" class="text-center text-muted">Belum ada user</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalAddUser" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="/users/store">
      <div class="modal-header">
        <h5 class="modal-title">Tambah User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Username <span class="text-danger">*</span></label>
          <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">Role <span class="text-danger">*</span></label>
          <select name="role" class="form-select" id="addRole" required>
            <option value="admin">Admin</option>
            <option value="penilai" selected>Penilai</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Password <span class="text-danger">*</span></label>
          <input type="password" name="password" class="form-control" required>
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
<div class="modal fade" id="modalEditUser" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="/users/update">
      <div class="modal-header">
        <h5 class="modal-title">Edit User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="editUserId">
        <div class="mb-3">
          <label class="form-label">Username <span class="text-danger">*</span></label>
          <input type="text" name="username" class="form-control" id="editUsername" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" id="editEmail">
        </div>
        <div class="mb-3">
          <label class="form-label">Role <span class="text-danger">*</span></label>
          <select name="role" class="form-select" id="editRole" required>
            <option value="admin">Admin</option>
            <option value="penilai">Penilai</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Password (kosongkan jika tidak mengubah)</label>
          <input type="password" name="password" class="form-control">
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
<div class="modal fade" id="modalDeleteUser" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" method="post" action="/users/delete">
      <div class="modal-header">
        <h5 class="modal-title text-danger">Hapus User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="deleteUserId">
        <p class="mb-1">Yakin hapus user berikut?</p>
        <div class="fw-semibold" id="deleteUsername"></div>
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
  const editModalEl = document.getElementById('modalEditUser');
  const deleteModalEl = document.getElementById('modalDeleteUser');
  const modalEdit = editModalEl ? new bootstrap.Modal(editModalEl) : null;
  const modalDelete = deleteModalEl ? new bootstrap.Modal(deleteModalEl) : null;
  document.querySelectorAll('.btn-edit-user').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('editUserId').value = btn.dataset.id || '';
      document.getElementById('editUsername').value = btn.dataset.username || '';
      document.getElementById('editEmail').value = btn.dataset.email || '';
      document.getElementById('editRole').value = btn.dataset.role || 'penilai';
      if (modalEdit) modalEdit.show();
    });
  });
  document.querySelectorAll('.btn-delete-user').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('deleteUserId').value = btn.dataset.id || '';
      document.getElementById('deleteUsername').textContent = btn.dataset.username || '';
      if (modalDelete) modalDelete.show();
    });
  });
});
</script>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/main.php'; ?>
