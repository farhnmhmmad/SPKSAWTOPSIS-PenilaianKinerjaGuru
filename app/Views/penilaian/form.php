<?php $title = ($mode ?? 'create') === 'edit' ? 'Ubah Penilaian' : 'Penilaian Baru'; ob_start(); ?>
<form method="post" action="<?= ($mode ?? 'create') === 'edit' ? '/penilaian/update' : '/penilaian/store' ?>">
  <?php if(($mode ?? 'create') === 'edit'): ?>
    <input type="hidden" name="id" value="<?= $row['id_penilaian'] ?? '' ?>">
  <?php endif; ?>
  <div class="mb-3">
    <label class="form-label">Nama Penilai <span class="text-danger">*</span></label>
    <input type="text" name="nama_penilai" class="form-control" required value="<?= htmlspecialchars($row['nama_penilai'] ?? '') ?>">
  </div>
  <div class="mb-3">
    <label class="form-label">NIP Penilai</label>
    <input type="text" name="nip_penilai" class="form-control" value="<?= htmlspecialchars($row['nip_penilai'] ?? '') ?>">
  </div>
  <div class="mb-3">
    <label class="form-label">Periode <span class="text-danger">*</span></label>
    <input type="text" name="periode" class="form-control" required value="<?= htmlspecialchars($row['periode'] ?? '') ?>">
  </div>
  <div class="mt-3 d-flex gap-2">
    <a href="/penilaian" class="btn btn-light">Batal</a>
    <button class="btn btn-primary" type="submit">Simpan</button>
  </div>
</form>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/main.php'; ?>
