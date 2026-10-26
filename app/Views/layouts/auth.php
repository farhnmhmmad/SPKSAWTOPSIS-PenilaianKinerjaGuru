<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title ?? 'Login') ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    :root{
      --brand:#2563eb;
      --border:#e5e7eb;
      --bg:#f5f7ff;
      --text:#0f172a;
      --muted:#64748b;
    }
    body{margin:0;font-family:system-ui,Arial,sans-serif;background:var(--bg);color:var(--text);}
    .auth-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:18px;}
    .panel{width:min(920px,100%);background:#fff;border:1px solid var(--border);border-radius:16px;display:grid;grid-template-columns:1fr 1fr;overflow:hidden;box-shadow:0 16px 50px rgba(37,99,235,.08);}
    .panel .info{padding:32px 26px;background:linear-gradient(160deg,rgba(37,99,235,.12),rgba(37,99,235,.04));}
    .panel .info h1{font-weight:800;font-size:26px;margin-bottom:8px;}
    .panel .info p{color:var(--muted);margin-bottom:16px;}
    .panel .info .logo img{width:100%;max-width:420px;max-height:320px;object-fit:contain;border-radius:12px;background:#fff;padding:12px;border:1px solid var(--border);}
    .panel .form-area{padding:32px 26px;}
    .alert{padding:10px;border-radius:10px;background:#fee2e2;color:#991b1b;border:1px solid #fecaca;}
    .btn-brand{display:inline-flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:12px 14px;border-radius:12px;border:1px solid #1d4ed8;background:var(--brand);color:#fff;font-weight:700;}
    .btn-brand:hover{background:#1d4ed8;}
    .minor{color:var(--muted);font-size:12px;margin-top:12px;text-align:center;}
    @media(max-width:880px){ .panel{grid-template-columns:1fr;} .panel .info{order:2;} .panel .form-area{order:1;} }
  </style>
</head>
<body>
  <div class="auth-wrap">
    <div class="panel">
      <div class="form-area">
        <h2 class="fw-bold mb-1">Masuk</h2>
        <div class="text-muted mb-3">Gunakan kredensial Anda</div>
        <?= $content ?? '' ?>
        <div class="minor">&copy; <?= date('Y') ?> Sistem Evaluasi Kinerja Guru</div>
      </div>
      <div class="info">
        <h1 class="title-main">
            Sistem Pendukung Keputusan
          </h1>
        <h2 class="title-sub">Evaluasi Kinerja Guru
          </h2>
        <p class="title-meta">Berbasis Web · Metode SAW & TOPSIS<br>SDN Lebak Bulus 04
          </p>
        <?php $logoUrl = trim($_ENV['APP_LOGO_URL'] ?? ''); if($logoUrl): ?>
          <div class="logo"><img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo Sekolah"></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>
