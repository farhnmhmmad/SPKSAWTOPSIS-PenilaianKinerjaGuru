<?php
$title = 'Data Periode Penilaian';
$rows = $data['data'] ?? $data ?? [];
if (!is_array($rows)) $rows = [];
$lockedMap = $lockedMap ?? [];
$lockedWarn = !empty($_GET['err']) && $_GET['err']==='locked';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
  </div>
  <button class="btn btn-primary btn-sm" id="btnNewPenilaian"><i class="bi bi-plus-lg me-1"></i>Tambah Periode</button>
</div>
<?php if($lockedWarn): ?>
  <div class="alert alert-warning">Perhitungan sudah dipublish. Archive semua metode terlebih dahulu untuk menghapus.</div>
<?php endif; ?>
<div class="table-responsive">
  <table class="table table-sm align-middle">
    <thead class="table-light">
      <tr>
        <th>No</th><th>Periode</th><th>NIP Penilai</th><th>Nama Penilai</th><th>Tanggal</th><th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php $no = 1; foreach($rows as $row): ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= htmlspecialchars($row['periode'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['nip_penilai'] ?? '') ?></td>
        <td>
          <?= htmlspecialchars($row['nama_penilai'] ?? '') ?>
        </td>
        <td><?= htmlspecialchars($row['tanggal_penilaian'] ?? '') ?></td>
        <td>
          <a class="btn btn-outline-primary btn-sm" href="/penilaian/nilai?id=<?= $row['id_penilaian'] ?? '' ?>"><i class="bi bi-pencil me-1"></i>Input Nilai</a>
          <a class="btn btn-outline-success btn-sm" target="_blank" href="/penilaian/cetak-periode?id=<?= $row['id_penilaian'] ?? '' ?>"><i class="bi bi-printer me-1"></i>Cetak</a>
          <button class="btn btn-outline-secondary btn-sm btn-edit-penilaian"
                  data-id="<?= $row['id_penilaian'] ?? '' ?>"
                  data-nama="<?= htmlspecialchars($row['nama_penilai'] ?? '', ENT_QUOTES) ?>"
                  data-nip="<?= htmlspecialchars($row['nip_penilai'] ?? '', ENT_QUOTES) ?>"
                  data-periode="<?= htmlspecialchars($row['periode'] ?? '', ENT_QUOTES) ?>">
            <i class="bi bi-pencil-square me-1"></i>Edit
          </button>
          <button class="btn btn-outline-danger btn-sm btn-del-penilaian"
                  data-id="<?= $row['id_penilaian'] ?? '' ?>"
                  data-nama="<?= htmlspecialchars($row['nama_penilai'] ?? '', ENT_QUOTES) ?>"
                  data-locked="<?= !empty($lockedMap[$row['id_penilaian']]) ? '1' : '0' ?>">
            <i class="bi bi-trash me-1"></i>Hapus
          </button>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if(!$rows): ?>
      <tr><td colspan="5" class="text-center text-muted">Belum ada data</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<!-- Modal Hapus Penilaian -->
<div class="modal fade" id="modalDelPenilaian" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="/penilaian/delete">
      <div class="modal-header">
        <h5 class="modal-title text-danger">Hapus Penilaian</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="delPenId">
        <p class="mb-1">Yakin hapus penilaian berikut?</p>
        <div class="fw-semibold" id="delPenNama"></div>
        <div class="text-warning small mt-2" id="delPenWarn" style="display:none;">Perhitungan sudah dipublish. Archive dulu untuk menghapus.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-danger" id="delPenSubmit">Hapus</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Create/Edit Penilaian -->
<div class="modal fade" id="modalPenilaian" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="post" id="formPenilaian" action="/penilaian/store">
      <div class="modal-header">
        <h5 class="modal-title" id="modalPenilaianTitle">Tambah Periode</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="penilaianId">
        <div class="mb-3">
          <label class="form-label">Periode <span class="text-danger">*</span></label>
          <input type="text" name="periode" id="penilaianPeriode" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">NIP Penilai</label>
          <input type="text" name="nip_penilai" id="penilaianNip" class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">Nama Penilai <span class="text-danger">*</span></label>
          <input type="text" name="nama_penilai" id="penilaianNama" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary" id="btnSavePenilaian">Simpan</button>
      </div>
    </form>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const modalEl = document.getElementById('modalDelPenilaian');
  const modal = modalEl ? new bootstrap.Modal(modalEl) : null;
  document.querySelectorAll('.btn-del-penilaian').forEach(btn => {
    btn.addEventListener('click', () => {
      const locked = btn.dataset.locked === '1';
      document.getElementById('delPenId').value = btn.dataset.id || '';
      document.getElementById('delPenNama').textContent = btn.dataset.nama || '';
      const warn = document.getElementById('delPenWarn');
      const submit = document.getElementById('delPenSubmit');
      if (warn && submit) {
        warn.style.display = locked ? 'block' : 'none';
        submit.disabled = locked;
      }
      if (modal) modal.show();
    });
  });
  const modalPenEl = document.getElementById('modalPenilaian');
  const modalPen = modalPenEl ? new bootstrap.Modal(modalPenEl) : null;
  const formPen = document.getElementById('formPenilaian');
  const inputId = document.getElementById('penilaianId');
  const inputNama = document.getElementById('penilaianNama');
  const inputNip = document.getElementById('penilaianNip');
  const inputPeriode = document.getElementById('penilaianPeriode');
  const titleEl = document.getElementById('modalPenilaianTitle');
  const btnNew = document.getElementById('btnNewPenilaian');

  const openCreate = () => {
    if (!modalPen || !formPen || !inputId || !inputNama || !inputNip || !inputPeriode || !titleEl) return;
    formPen.action = '/penilaian/store';
    inputId.value = '';
    inputNama.value = '';
    inputNip.value = '';
    inputPeriode.value = '';
    titleEl.textContent = 'Tambah Periode';
    modalPen.show();
  };
  if (btnNew) btnNew.addEventListener('click', (e) => { e.preventDefault(); openCreate(); });

  document.querySelectorAll('.btn-edit-penilaian').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      if (!modalPen || !formPen || !inputId || !inputNama || !inputNip || !inputPeriode || !titleEl) return;
      formPen.action = '/penilaian/update';
      inputId.value = btn.dataset.id || '';
      inputNama.value = btn.dataset.nama || '';
      inputNip.value = btn.dataset.nip || '';
      inputPeriode.value = btn.dataset.periode || '';
      titleEl.textContent = 'Edit Periode';
      modalPen.show();
    });
  });
});
</script>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/main.php'; ?>
