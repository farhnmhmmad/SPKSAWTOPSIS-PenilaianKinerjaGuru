<?php $title = 'Login'; ob_start(); ?>
  <form class="form" method="post" action="/login" novalidate>
    <?php if (!empty($error)): ?><div class="alert">Login gagal. Periksa kembali.</div><?php endif; ?>

    <div class="mb-3">
      <label for="username" class="form-label">Username</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-person"></i></span>
        <input id="username" class="form-control" type="text" name="username" placeholder="Masukkan username" required autofocus>
      </div>
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
        <input id="password" class="form-control" type="password" name="password" placeholder="Masukkan password" required>
      </div>
    </div>

    <div class="d-grid gap-2 mt-3">
      <button class="btn-brand" type="submit"><i class="bi bi-box-arrow-in-right"></i> Masuk</button>
      <a class="btn btn-outline-secondary" href="/guest"><i class="bi bi-eye"></i> Masuk sebagai Guest</a>
    </div>
  </form>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/auth.php'; ?>
