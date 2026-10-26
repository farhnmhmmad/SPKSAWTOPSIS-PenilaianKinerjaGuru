<?php $title = ($mode ?? 'create') === 'edit' ? 'Ubah Indikator' : 'Tambah Indikator'; ob_start(); ?>
<form method="post" action="<?= ($mode ?? 'create') === 'edit' ? '/indikator/update' : '/indikator/store' ?>">
  <?php if(($mode ?? 'create') === 'edit'): ?>
    <input type="hidden" name="id" value="<?= $row['id_indikator'] ?? '' ?>">
  <?php endif; ?>
  <div class="mb-3">
    <label class="form-label">Subkriteria <span class="text-danger">*</span></label>
    <select name="id_subkriteria" class="form-select" required>
      <?php foreach($sub as $s): ?>
        <option value="<?= $s['id_subkriteria'] ?>" <?= (($row['id_subkriteria'] ?? null)==$s['id_subkriteria'])?'selected':'' ?>>
          <?= htmlspecialchars($s['nama_kriteria'].' - '.$s['nama_subkriteria']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <?php $isEdit = (($mode ?? 'create') === 'edit'); ?>
  <?php if($isEdit): ?>
    <div class="mb-3">
      <label class="form-label">Teks Indikator <span class="text-danger">*</span></label>
      <textarea name="teks" class="form-control" rows="3" required><?= htmlspecialchars($row['nama_indikator'] ?? '') ?></textarea>
    </div>
  <?php else: ?>
    <div id="indikator-rows">
      <div class="indikator-row border rounded-3 p-3 mb-2">
        <div class="mb-2">
          <label class="form-label">Teks Indikator <span class="text-danger">*</span></label>
          <textarea name="indikator[0][teks]" class="form-control" rows="2" required></textarea>
        </div>
        <button type="button" class="btn btn-outline-danger btn-sm mt-2 btn-remove" style="display:none">Hapus</button>
      </div>
    </div>
    <button type="button" class="btn btn-outline-secondary btn-sm mb-2" id="btn-add-row"><i class="bi bi-plus-lg me-1"></i>Tambah Indikator</button>
  <?php endif; ?>

  <div class="mt-3 d-flex gap-2">
    <a href="/indikator" class="btn btn-light">Batal</a>
    <button class="btn btn-primary" type="submit">Simpan</button>
  </div>
</form>
<?php if(!$isEdit): ?>
<script>
  (function(){
    const container = document.getElementById('indikator-rows');
    const btnAdd = document.getElementById('btn-add-row');
    if(!container || !btnAdd) return;
    btnAdd.addEventListener('click', function(){
      const idx = container.querySelectorAll('.indikator-row').length;
      const tpl = document.createElement('div');
      tpl.className = 'indikator-row border rounded-3 p-3 mb-2';
      tpl.innerHTML = `
        <div class="mb-2">
          <label class="form-label">Teks Indikator</label>
          <textarea name="indikator[${idx}][teks]" class="form-control" rows="2" required></textarea>
        </div>
        <button type="button" class="btn btn-outline-danger btn-sm mt-2 btn-remove">Hapus</button>
      `;
      container.appendChild(tpl);
      tpl.querySelector('.btn-remove').addEventListener('click', function(){
        tpl.remove();
      });
    });
    container.addEventListener('DOMNodeInserted', () => toggleRemove());
    container.addEventListener('DOMNodeRemoved', () => toggleRemove());
    function toggleRemove(){
      const rows = container.querySelectorAll('.indikator-row');
      rows.forEach((row, idx) => {
        const btn = row.querySelector('.btn-remove');
        if (!btn) return;
        btn.style.display = rows.length > 1 ? 'inline-block' : 'none';
      });
    }
  })();
</script>
<?php endif; ?>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/main.php'; ?>
