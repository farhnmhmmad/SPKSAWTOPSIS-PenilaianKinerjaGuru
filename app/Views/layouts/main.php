<?php $user = $_SESSION['user'] ?? null; ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title ?? 'SPK Evaluasi') ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    :root{
      --brand:#2563eb;
      --brand-soft:#e0e7ff;
      --bg:#f6f7fb;
      --text:#0f172a;
      --muted:#64748b;
      --border:#e5e7eb;
    }
    body{font-family:system-ui,Arial,sans-serif;background:var(--bg);color:var(--text);}
    .topbar{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;background:linear-gradient(120deg,var(--brand),#1d4ed8);color:#fff;position:sticky;top:0;z-index:2;box-shadow:0 3px 12px rgba(0,0,0,.08);}
    .topbar .title{font-weight:800;font-size:20px;letter-spacing:.3px;}
    .logout{color:#fff;text-decoration:none;font-weight:600;border:1px solid rgba(255,255,255,.35);padding:8px 12px;border-radius:10px;background:rgba(255,255,255,.1);}
    .wrap{display:flex;min-height:calc(100vh - 52px);}
    .sidebar{width:240px;background:#fff;border-right:1px solid var(--border);position:fixed;top:52px;left:0;height:calc(100vh - 52px);overflow-y:auto;z-index:1;}
    .sidebar .menu{padding:14px;}
    .logo-box{display:flex;align-items:center;justify-content:center;padding:10px 0 16px;}
    .logo-box img{max-width:120px;max-height:80px;object-fit:contain;}
    .menu a{display:block;text-decoration:none;color:#1f2937;padding:10px 12px;margin-bottom:8px;border:1px solid var(--border);border-radius:10px;font-weight:600;background:#fff;}
    .menu a.active{background:var(--brand);color:#fff;border-color:var(--brand);}
    .menu small{display:block;color:var(--muted);font-weight:500;margin-bottom:4px;}
    .content{flex:1;padding:22px;margin-left:240px;}
    .card{border:1px solid var(--border);border-radius:12px;box-shadow:0 8px 22px rgba(37,99,235,.07);}
    table.table{margin:0;border:1px solid var(--border);}
    table.table th,table.table td{border:1px solid var(--border);} 
    .badge-tag{background:var(--brand-soft);color:#1d4ed8;font-weight:700;padding:6px 10px;border-radius:10px;}
    @media(max-width:980px){
      .wrap{flex-direction:column;}
      .sidebar{width:100%;border-right:none;border-bottom:1px solid var(--border);position:relative;top:0;left:0;height:auto;overflow:visible;}
      .menu a{display:inline-block;width:48%;margin-right:2%;text-align:center;}
      .content{padding:14px;margin-left:0;}
    }
  </style>
</head>
<body>
  <div class="topbar">
    <div class="title"><i class="bi bi-mortarboard-fill me-2"></i>Evaluasi Kinerja Guru</div>
    <?php if($user): ?>
      <a class="logout" href="/logout"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
    <?php endif; ?>
  </div>
  <div class="wrap">
    <aside class="sidebar">
      <div class="menu">
        <div class="logo-box">
          <img src="/uploads/logo-sekolah.png" alt="Logo" onerror="this.style.display='none'">
        </div>
        <?php $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH); ?>
        <?php $role = $user['role'] ?? ''; ?>
        <?php if($role === 'admin'): ?>
          <small>Menu Utama</small>
          <a href="/dashboard" class="<?= $path==='/dashboard'?'active':'' ?>"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
          <a href="/guru" class="<?= str_starts_with($path,'/guru')?'active':'' ?>"><i class="bi bi-people me-1"></i>Guru</a>
          <a href="/kriteria" class="<?= str_starts_with($path,'/kriteria')?'active':'' ?>"><i class="bi bi-list-check me-1"></i>Kriteria</a>
          <a href="/subkriteria" class="<?= str_starts_with($path,'/subkriteria')?'active':'' ?>"><i class="bi bi-diagram-3 me-1"></i>Subkriteria</a>
          <a href="/indikator" class="<?= str_starts_with($path,'/indikator')?'active':'' ?>"><i class="bi bi-ui-checks-grid me-1"></i>Indikator</a>
          <a href="/penilaian" class="<?= str_starts_with($path,'/penilaian')?'active':'' ?>"><i class="bi bi-clipboard-check me-1"></i>Penilaian Guru</a>
          <small>Perhitungan</small>
          <a href="/perhitungan" class="<?= str_starts_with($path,'/perhitungan')?'active':'' ?>"><i class="bi bi-cpu me-1"></i>Perhitungan</a>
          <a href="/peringkat" class="<?= str_starts_with($path,'/peringkat')?'active':'' ?>"><i class="bi bi-bar-chart-steps me-1"></i>Peringkat</a>
          <small>Akun</small>
          <a href="/profile" class="<?= str_starts_with($path,'/profile')?'active':'' ?>"><i class="bi bi-person-circle me-1"></i>Profil Saya</a>
          <a href="/users" class="<?= str_starts_with($path,'/users')?'active':'' ?>"><i class="bi bi-person-gear me-1"></i>Kelola User</a>
        <?php elseif($role === 'penilai'): ?>
          <small>Menu</small>
          <a href="/dashboard" class="<?= $path==='/dashboard'?'active':'' ?>"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
          <a href="/penilaian" class="<?= str_starts_with($path,'/penilaian')?'active':'' ?>"><i class="bi bi-clipboard-check me-1"></i>Penilaian Guru</a>
          <a href="/peringkat" class="<?= str_starts_with($path,'/peringkat')?'active':'' ?>"><i class="bi bi-bar-chart-steps me-1"></i>Peringkat</a>
          <small>Akun</small>
          <a href="/profile" class="<?= str_starts_with($path,'/profile')?'active':'' ?>"><i class="bi bi-person-circle me-1"></i>Profil Saya</a>
        <?php else: ?>
          <small>Menu</small>
          <a href="/peringkat" class="<?= str_starts_with($path,'/peringkat')?'active':'' ?>"><i class="bi bi-bar-chart-steps me-1"></i>Peringkat</a>
        <?php endif; ?>
      </div>
    </aside>
    <main class="content">
      <?php if(!empty($flashMessages ?? [])): ?>
        <?php foreach($flashMessages as $f): ?>
          <?php $t = ($f['type'] ?? 'success'); $cls = $t==='error'?'danger':($t==='warning'?'warning':'success'); ?>
          <div class="alert alert-<?= $cls ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($f['msg'] ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
      <div class="card p-4 mb-3">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h4 class="mb-0"><?= htmlspecialchars($title ?? 'Halaman') ?></h4>
          <?php if($user): ?><span class="badge-tag"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($user['username']) ?></span><?php endif; ?>
        </div>
        <?= $content ?? '' ?>
      </div>
    </main>
  </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</html>
