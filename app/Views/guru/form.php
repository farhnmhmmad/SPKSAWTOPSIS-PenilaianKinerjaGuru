<?php $title = ($mode ?? 'create') === 'edit' ? 'Ubah Guru' : 'Tambah Guru'; ob_start(); ?>
<form method="post" action="<?= ($mode ?? 'create') === 'edit' ? '/guru/update' : '/guru/store' ?>">
  <?php if(($mode ?? 'create') === 'edit'): ?>
    <input type="hidden" name="id" value="<?= $row['id_guru'] ?? '' ?>">
  <?php endif; ?>
  <div class="mb-3">
    <label class="form-label">Nama Guru</label>
    <input type="text" name="nama_guru" class="form-control" required value="<?= htmlspecialchars($row['nama_guru'] ?? '') ?>">
  </div>
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">NIP</label>
      <input type="text" name="nip_guru" class="form-control" required value="<?= htmlspecialchars($row['nip_guru'] ?? '') ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Jenis Kelamin</label>
      <?php $jk = $row['jenis_kelamin'] ?? ''; ?>
      <select name="jenis_kelamin" class="form-select" required>
        <option value="">-- Pilih --</option>
        <option value="L" <?= $jk==='L'?'selected':'' ?>>Laki-laki</option>
        <option value="P" <?= $jk==='P'?'selected':'' ?>>Perempuan</option>
      </select>
    </div>
  </div>
  <div class="mt-3 d-flex gap-2">
    <a href="/guru" class="btn btn-light">Batal</a>
    <button class="btn btn-primary" type="submit">Simpan</button>
  </div>
</form>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/main.php'; ?>
