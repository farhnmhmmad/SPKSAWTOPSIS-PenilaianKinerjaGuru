<?php
$isEdit = ($mode ?? 'create') === 'edit';
$title = $isEdit ? 'Ubah Subkriteria' : 'Tambah Subkriteria';
ob_start();
?>
<form method="post" action="<?= $isEdit ? '/subkriteria/update' : '/subkriteria/store' ?>">
  <?php if($isEdit): ?>
    <input type="hidden" name="id" value="<?= $row['id_subkriteria'] ?? '' ?>">
  <?php endif; ?>
  <div class="mb-3">
    <label class="form-label">Kriteria <span class="text-danger">*</span></label>
    <select name="id_kriteria" class="form-select" required>
      <?php foreach($kriteria as $k): ?>
        <option value="<?= $k['id_kriteria'] ?>" <?= (($row['id_kriteria'] ?? null)==$k['id_kriteria'])?'selected':'' ?>><?= htmlspecialchars($k['nama_kriteria']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <?php if($isEdit): ?>
    <div class="mb-3">
      <label class="form-label">Nama Subkriteria <span class="text-danger">*</span></label>
      <input type="text" name="nama_subkriteria" class="form-control" required value="<?= htmlspecialchars($row['nama_subkriteria'] ?? '') ?>">
    </div>
  <?php else: ?>
    <div id="subkriteria-rows">
      <div class="sub-row border rounded-3 p-3 mb-2">
        <div class="mb-2">
          <label class="form-label">Nama Subkriteria <span class="text-danger">*</span></label>
          <input type="text" name="subkriteria[0][nama_subkriteria]" class="form-control" required>
        </div>
        <button type="button" class="btn btn-outline-danger btn-sm mt-2 btn-remove" style="display:none">Hapus</button>
      </div>
    </div>
    <button type="button" class="btn btn-outline-secondary btn-sm mb-2" id="btn-add-sub"><i class="bi bi-plus-lg me-1"></i>Tambah Subkriteria</button>
  <?php endif; ?>

  <div class="mt-3 d-flex gap-2">
    <a href="/subkriteria" class="btn btn-light">Batal</a>
    <button class="btn btn-primary" type="submit">Simpan</button>
  </div>
</form>
<?php if(!$isEdit): ?>
<script>
  (function(){
    const container = document.getElementById('subkriteria-rows');
    const btnAdd = document.getElementById('btn-add-sub');
    if(!container || !btnAdd) return;
    btnAdd.addEventListener('click', function(){
      const idx = container.querySelectorAll('.sub-row').length;
      const row = document.createElement('div');
      row.className = 'sub-row border rounded-3 p-3 mb-2';
      row.innerHTML = `
        <div class="mb-2">
          <label class="form-label">Nama Subkriteria <span class="text-danger">*</span></label>
          <input type="text" name="subkriteria[${idx}][nama_subkriteria]" class="form-control" required>
        </div>
        <button type="button" class="btn btn-outline-danger btn-sm mt-2 btn-remove">Hapus</button>
      `;
      container.appendChild(row);
      row.querySelector('.btn-remove').addEventListener('click', function(){
        row.remove();
        toggleRemove();
      });
      toggleRemove();
    });
    const toggleRemove = () => {
      const rows = container.querySelectorAll('.sub-row');
      rows.forEach((row) => {
        const btn = row.querySelector('.btn-remove');
        if (!btn) return;
        btn.style.display = rows.length > 1 ? 'inline-block' : 'none';
      });
    };
    toggleRemove();
  })();
</script>
<?php endif; ?>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/main.php'; ?>
