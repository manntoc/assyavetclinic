<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>assyavetclinic.com - Login / Register</title>
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="48x48" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('logo-web.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('logo-web.png') }}">

    <!-- Google Fonts Link For Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@48,400,0,0">

    <link rel="stylesheet" href="{{ asset('assets/frontend/css/loginregis.css') }}">
</head>
<body>
   <!--// Services Section Start //-->
   <div class="container">
    <input type="checkbox" id="check">
    <div class="login form">
      <header>Masuk <br> <h3 style="color: #666"><span style="color: #2B8BBB">Assya</span> Vet Clinic</h3></header>
      
      <!-- ALERT ERROR & STATUS NOTIFICATION -->
      @if ($errors->any())
        <div style="background-color: #fee2e2; border: 1px solid #f87171; color: #991b1b; padding: 12px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; line-height: 1.4;">
          <div style="font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
            <span>⚠️</span> Gagal Masuk:
          </div>
          <ul style="margin: 0; padding-left: 18px;">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      @if (session('error'))
        <div style="background-color: #fee2e2; border: 1px solid #f87171; color: #991b1b; padding: 12px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; line-height: 1.4; display: flex; align-items: center; gap: 6px;">
          <span>⚠️</span>
          <div>{{ session('error') }}</div>
        </div>
      @endif

      @if (session('status'))
        <div style="background-color: #dcfce7; border: 1px solid #86efac; color: #166534; padding: 12px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; line-height: 1.4; display: flex; align-items: center; gap: 6px;">
          <span>✅</span>
          <div>{{ session('status') }}</div>
        </div>
      @endif

      <form method="POST" action="{{ route('loginuser') }}">
        @csrf
        <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required autofocus>
        <input type="password" name="password" placeholder="********" required>
        <input type="submit" class="button" value="Masuk">
      </form>
      <div class="signup">
        <span class="signup">Belum Punya Akun?
         <label for="check">Daftar</label>
        </span> <br>
        <span class="signup">Kembali Ke Beranda
         <a href="{{ asset('/') }}">Kembali</a>
        </span>
      </div>
    </div>
    <div class="registration form">
      <header>Daftar</header>
      <form method="POST" action="{{ route('registeruser') }}">
        @csrf
        <input type="text" name="name" placeholder="Masukkan Nama Anda" required>
        <input type="email" name="email" placeholder="Masukkan Email Anda" required>
        <input type="password" name="password" placeholder="Tuliskan kata sandi yang kuat" required>
        <input type="password" name="password_confirmation"  placeholder="Konfirmasi kembali kata sandi anda" required>
        <input type="submit" class="button" value="Daftar">
      </form>
      <div class="signup">
        <span class="signup">Sudah Punya Akun?
         <label for="check">Masuk</label>
        </span>
      </div>
    </div>
  </div>
  <!--// Services Section End //-->
</body>
<!--// loginregis Js //-->
<script src="{{ asset('assets/frontend/js/loginregis.js') }}"></script>
</html>