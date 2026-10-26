<?php $title = ($mode ?? 'create') === 'edit' ? 'Ubah Kriteria' : 'Tambah Kriteria'; ob_start(); ?>
<form method="post" action="<?= ($mode ?? 'create') === 'edit' ? '/kriteria/update' : '/kriteria/store' ?>">
  <?php if(($mode ?? 'create') === 'edit'): ?>
    <input type="hidden" name="id" value="<?= $row['id'] ?? '' ?>">
  <?php endif; ?>
  <div class="row g-3">
    <div class="col-md-4">
      <label class="form-label">Kode *</label>
    <input type="text" name="kode" class="form-control" required value="<?= htmlspecialchars($row['kode_kriteria'] ?? '') ?>">
    </div>
    <div class="col-md-8">
      <label class="form-label">Nama *</label>
    <input type="text" name="nama" class="form-control" required value="<?= htmlspecialchars($row['nama_kriteria'] ?? '') ?>">
    </div>
  </div>
  <div class="row g-3 mt-1">
    <div class="col-md-4">
      <label class="form-label">Bobot (0-1) *</label>
      <input type="number" name="bobot" step="0.01" min="0" max="1" class="form-control" required value="<?= htmlspecialchars($row['bobot_kriteria'] ?? '') ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Tipe *</label>
      <select name="tipe" class="form-select" required>
        <option value="benefit" <?= (($row['tipe_kriteria'] ?? '')==='benefit')?'selected':'' ?>>Benefit</option>
        <option value="cost" <?= (($row['tipe_kriteria'] ?? '')==='cost')?'selected':'' ?>>Cost</option>
      </select>
    </div>
  </div>
  <div class="mt-3 d-flex gap-2">
    <a href="/kriteria" class="btn btn-light">Batal</a>
    <button class="btn btn-primary" type="submit">Simpan</button>
  </div>
</form>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/main.php'; ?>
