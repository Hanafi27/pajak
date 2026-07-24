<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Portal Bapenda - Login</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    * { box-sizing: border-box; }
    body { min-height: 100vh; overflow: auto; background: #fff; color: #171717; }
    .login-page {
      min-height: 100vh;
      display: grid;
      place-items: center;
      padding: 32px 16px;
      background: #fafafa;
    }
    .login-card {
      width: 100%;
      max-width: 440px;
      background: #fff;
      border: 1px solid #dfdfdf;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0,0,0,.08);
      padding: 32px;
    }
    .login-header { text-align: center; margin-bottom: 26px; }
    .login-logo { width: 78px; height: 78px; object-fit: contain; display: block; margin: 0 auto 12px; }
    .instansi { margin: 0; font-size: 36px; font-weight: 500; color: #171717; line-height: 1.15; letter-spacing: -.72px; }
    .instansi-sub { display: inline-flex; margin: 12px 0 0; border-radius: 9999px; background: #3ecf8e; color: #171717; padding: 2px 8px; font-size: 12px; font-weight: 500; }
    .field-wrap { margin-bottom: 14px; }
    .field-label { display: block; margin-bottom: 8px; font-size: 14px; font-weight: 500; color: #212121; }
    .field-input {
      width: 100%;
      min-height: 38px;
      border: 1px solid #dfdfdf;
      border-radius: 6px;
      padding: 8px 12px;
      font-size: 16px;
      font-weight: 400;
      color: #171717;
      outline: none;
      transition: .2s;
      background: #fff;
    }
    .field-input:focus { border-color: #24b47e; box-shadow: 0 0 0 3px rgba(62,207,142,.18); }
    .forgot-wrap { text-align: right; margin: 6px 0 16px; }
    .forgot-link { font-size: 14px; font-weight: 500; color: #171717; text-decoration: underline; text-decoration-color: #c7c7c7; text-underline-offset: 3px; }
    .forgot-link:hover { text-decoration-color: #171717; }
    .submit-btn {
      width: 100%;
      min-height: 38px;
      border: 0;
      border-radius: 6px;
      background: #3ecf8e;
      color: #171717;
      font-size: 14px;
      font-weight: 500;
      padding: 8px 16px;
      cursor: pointer;
      transition: .2s;
    }
    .submit-btn:hover { background: #24b47e; }
    .alert-error, .alert-success { margin-bottom: 14px; padding: 10px 12px; border-radius: 8px; font-size: 14px; font-weight: 400; }
    .alert-error { background: #fff1f2; border: 1px solid #fecdd3; color: #be123c; }
    .alert-success { background: rgba(62,207,142,.14); border: 1px solid rgba(62,207,142,.32); color: #0f6b49; }
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
        <div class="alert-error">{{ session('error') }}</div>
      @endif
      @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
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
          <a href="{{ route('password.forgot') }}" class="forgot-link">Lupa Kata Sandi?</a>
        </div>

        <button type="submit" class="submit-btn">Masuk</button>
      </form>
    </section>
  </div>
</body>
</html>

