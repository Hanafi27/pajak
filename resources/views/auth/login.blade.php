<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Portal Bapenda - Login</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;700&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    :root {
      --brand-teal: #0f5f61;
      --brand-green: #00945b;
      --brand-pink: #e63f75;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      min-height: 100vh;
      font-family: "IBM Plex Sans", sans-serif;
      background: #edf6f5;
      color: #1e293b;
    }
    .login-page {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px 16px;
    }
    .login-card {
      width: 100%;
      max-width: 450px;
      background: #fff;
      border: 1px solid #dbe4ea;
      border-radius: 18px;
      box-shadow: 0 2px 12px rgba(15, 23, 42, .08);
      padding: 28px 24px;
    }
    .login-header {
      text-align: center;
      margin-bottom: 20px;
    }
    .login-logo {
      width: 84px;
      height: 84px;
      object-fit: contain;
      display: block;
      margin: 0 auto 8px;
    }
    .instansi {
      margin: 0;
      font-family: "Sora", sans-serif;
      font-weight: 700;
      font-size: 23px;
      color: var(--brand-teal);
      line-height: 1.1;
    }
    .instansi-sub {
      margin: 2px 0 10px;
      font-size: 11px;
      letter-spacing: .12em;
      text-transform: uppercase;
      color: var(--brand-pink);
      font-weight: 600;
    }
    .login-title {
      margin: 0;
      font-family: "Sora", sans-serif;
      font-size: 34px;
      font-weight: 700;
      color: #1e293b;
      line-height: 1.15;
    }
    .field-wrap { margin-bottom: 14px; }
    .field-label {
      display: block;
      margin-bottom: 8px;
      font-size: 14px;
      font-weight: 600;
      color: #475569;
    }
    .field-input {
      width: 100%;
      border: 1px solid #cbd5e1;
      border-radius: 12px;
      padding: 12px 14px;
      font-size: 16px;
      outline: none;
      transition: .2s;
      background: #fff;
    }
    .field-input:focus {
      border-color: var(--brand-green);
      box-shadow: 0 0 0 2px rgba(0, 148, 91, .18);
    }
    .forgot-wrap {
      text-align: right;
      margin: 6px 0 14px;
    }
    .forgot-link {
      font-size: 14px;
      font-weight: 600;
      color: var(--brand-teal);
      text-decoration: none;
    }
    .forgot-link:hover { text-decoration: underline; }
    .submit-btn {
      width: 100%;
      border: 0;
      border-radius: 12px;
      background: var(--brand-green);
      color: #fff;
      font-size: 20px;
      font-weight: 700;
      padding: 12px 14px;
      cursor: pointer;
      transition: .2s;
    }
    .submit-btn:hover { opacity: .92; }
    .alert-error {
      margin-bottom: 14px;
      padding: 10px 12px;
      border-radius: 10px;
      background: #fff1f2;
      border: 1px solid #fecdd3;
      font-size: 14px;
      color: #be123c;
    }
    .demo-text {
      margin-top: 16px;
      padding-top: 14px;
      border-top: 1px solid #e2e8f0;
      text-align: center;
      font-size: 13px;
      color: #64748b;
    }
  </style>
</head>
<body>
  <div class="login-page">
    <section class="login-card">
      <div class="login-header">
        <img src="{{ asset('assets/logo-transparent.png') }}" alt="Logo Bapenda" class="login-logo">
        <p class="instansi">BAPENDA</p>
        <p class="instansi-sub">Kabupaten Bandung</p>
      </div>

      @if(session('error'))
        <div class="alert-error">
          {{ session('error') }}
        </div>
      @endif
      @if(session('success'))
        <div style="margin-bottom:14px;padding:10px 12px;border-radius:10px;background:#ecfdf5;border:1px solid #a7f3d0;font-size:14px;color:#065f46;">
          {{ session('success') }}
        </div>
      @endif

      <form method="post" action="{{ route('login.process') }}">
        @csrf
        <div class="field-wrap">
          <label class="field-label">Username</label>
          <input name="username" value="{{ old('username') }}" required placeholder="Masukkan username" class="field-input">
        </div>

        <div class="field-wrap">
          <label class="field-label">Kata Sandi</label>
          <input type="password" name="password" required placeholder="Masukkan kata sandi" class="field-input">
        </div>

        <div class="forgot-wrap">
          <a href="{{ route('password.forgot') }}" class="forgot-link">Lupa Kata Sandi ?</a>
        </div>

        <button type="submit" class="submit-btn">Masuk</button>
      </form>

      {{-- <div class="demo-text">
        Akun demo: <strong style="color:#334155">petugas/password123</strong> &bull; <strong style="color:#334155">pimpinan/password123</strong>
      </div> --}}
    </section>
  </div>
</body>
</html>
