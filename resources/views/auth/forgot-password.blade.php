<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Portal Bapenda - Lupa Kata Sandi</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;700&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    :root { --brand-green: #00945b; --brand-teal: #0f5f61; }
    * { box-sizing: border-box; }
    body { margin: 0; min-height: 100vh; background: #edf6f5; font-family: "IBM Plex Sans", sans-serif; color: #1e293b; }
    .page { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px 16px; }
    .card { width: 100%; max-width: 450px; background: #fff; border: 1px solid #dbe4ea; border-radius: 18px; box-shadow: 0 2px 12px rgba(15,23,42,.08); padding: 28px 24px; }
    .logo { width: 78px; height: 78px; object-fit: contain; display: block; margin: 0 auto 8px; }
    h1 { margin: 0 0 16px; text-align: center; font-family: "Sora", sans-serif; font-size: 30px; color: #1e293b; }
    .subtitle { margin: 0 0 18px; text-align: center; color: #64748b; font-size: 14px; }
    .field-wrap { margin-bottom: 14px; }
    .label { display: block; margin-bottom: 8px; font-size: 14px; color: #475569; font-weight: 600; }
    .input { width: 100%; border: 1px solid #cbd5e1; border-radius: 12px; padding: 12px 14px; font-size: 16px; outline: none; transition: .2s; }
    .input:focus { border-color: var(--brand-green); box-shadow: 0 0 0 2px rgba(0,148,91,.18); }
    .error { margin-bottom: 12px; padding: 10px 12px; border-radius: 10px; border: 1px solid #fecdd3; background: #fff1f2; color: #be123c; font-size: 14px; }
    .btn { width: 100%; border: 0; border-radius: 12px; padding: 12px 14px; font-size: 18px; font-weight: 700; background: var(--brand-green); color: #fff; cursor: pointer; transition: .2s; }
    .btn:hover { opacity: .92; }
    .back { margin-top: 14px; text-align: center; }
    .back a { color: var(--brand-teal); text-decoration: none; font-weight: 600; font-size: 14px; }
    .back a:hover { text-decoration: underline; }
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
