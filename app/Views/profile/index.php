<?php
$title = $title ?? 'Profil Saya';
$user = $user ?? [];
$username = htmlspecialchars($user['username'] ?? '-');
$email = $user['email'] ?? '';
$email = $email === '' || is_null($email) ? '-' : htmlspecialchars($email);
$role = htmlspecialchars($user['role'] ?? '-');
$created = htmlspecialchars(substr($user['created_at'] ?? '', 0, 10));
$id = $user['id'] ?? '';
ob_start();
?>
<div class="border rounded-3 p-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold mb-0">Informasi Akun</h6>
    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditProfile">
      <i class="bi bi-pencil-square me-1"></i>Edit
    </button>
  </div>
  <dl class="row mb-0">
    <dt class="col-sm-3 text-muted">Username :</dt><dd class="col-sm-9"><?= $username ?></dd>
    <dt class="col-sm-3 text-muted">Email :</dt><dd class="col-sm-9"><?= $email ?></dd>
    <dt class="col-sm-3 text-muted">Role :</dt><dd class="col-sm-9 text-uppercase"><?= $role ?></dd>
    <dt class="col-sm-3 text-muted">Dibuat :</dt><dd class="col-sm-9"><?= $created ?: '-' ?></dd>
  </dl>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEditProfile" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="/profile/update">
      <div class="modal-header">
        <h5 class="modal-title">Edit Profil</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" value="<?= htmlspecialchars((string)$id) ?>">
        <div class="mb-3">
          <label class="form-label">Username :</label>
          <input type="text" name="username" class="form-control" value="<?= $username ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email :</label>
          <input type="email" name="email" class="form-control" value="<?= $email === '-' ? '' : $email ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Password Baru :</label>
          <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
      </div>
    </form>
  </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/main.php'; ?>
