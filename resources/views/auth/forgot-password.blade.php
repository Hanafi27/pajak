<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Portal Bapenda - Lupa Kata Sandi</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    * { box-sizing: border-box; }
    body { min-height: 100vh; overflow: auto; background: #fff; color: #171717; }
    .page { min-height: 100vh; display: grid; place-items: center; padding: 32px 16px; background: #fafafa; }
    .card { width: 100%; max-width: 440px; background: #fff; border: 1px solid #dfdfdf; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,.08); padding: 32px; }
    .logo { width: 74px; height: 74px; object-fit: contain; display: block; margin: 0 auto 12px; }
    h1 { margin: 0 0 10px; text-align: center; font-size: 36px; font-weight: 500; color: #171717; letter-spacing: -.72px; }
    .subtitle { margin: 0 0 24px; text-align: center; color: #707070; font-size: 16px; font-weight: 400; line-height: 1.5; }
    .field-wrap { margin-bottom: 14px; }
    .label { display: block; margin-bottom: 8px; font-size: 14px; color: #212121; font-weight: 500; }
    .input { width: 100%; min-height: 38px; border: 1px solid #dfdfdf; border-radius: 6px; padding: 8px 12px; font-size: 16px; font-weight: 400; color: #171717; outline: none; transition: .2s; }
    .input:focus { border-color: #24b47e; box-shadow: 0 0 0 3px rgba(62,207,142,.18); }
    .error { margin-bottom: 12px; padding: 10px 12px; border-radius: 8px; border: 1px solid #fecdd3; background: #fff1f2; color: #be123c; font-size: 14px; font-weight: 400; }
    .btn { width: 100%; min-height: 38px; border: 0; border-radius: 6px; padding: 8px 16px; font-size: 14px; font-weight: 500; background: #3ecf8e; color: #171717; cursor: pointer; transition: .2s; }
    .btn:hover { background: #24b47e; }
    .back { margin-top: 16px; text-align: center; }
    .back a { color: #171717; text-decoration: underline; text-decoration-color: #c7c7c7; text-underline-offset: 3px; font-weight: 500; font-size: 14px; }
    .back a:hover { text-decoration-color: #171717; }
  </style>
</head>
<body>
  <main class="page">
    <section class="card">
      <img src="{{ asset('assets/logo-transparent.png') }}" alt="Logo Bapenda" class="logo">
      <h1>Lupa Kata Sandi</h1>
      <p class="subtitle">Masukkan username dan kata sandi baru untuk melanjutkan.</p>

      @if(session('error'))
        <div class="error">{{ session('error') }}</div>
      @endif
      @if($errors->any())
        <div class="error">{{ $errors->first() }}</div>
      @endif

      <form method="post" action="{{ route('password.forgot.process') }}">
        @csrf
        <div class="field-wrap">
          <label class="label">Username</label>
          <input name="username" value="{{ old('username') }}" required class="input" placeholder="Masukkan username">
        </div>
        <div class="field-wrap">
          <label class="label">Password Baru</label>
          <input type="password" name="password" required class="input" placeholder="Minimal 8 karakter">
        </div>
        <div class="field-wrap">
          <label class="label">Konfirmasi Password Baru</label>
          <input type="password" name="password_confirmation" required class="input" placeholder="Ulangi password baru">
        </div>
        <button type="submit" class="btn">Simpan Password Baru</button>
      </form>

      <div class="back"><a href="{{ route('login.form') }}">Kembali ke Login</a></div>
    </section>
  </main>
</body>
</html>

