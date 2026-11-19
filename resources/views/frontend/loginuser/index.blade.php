<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assyavetclinic - Login / Register</title>
@if (!empty($general_site_image->favicon_image))
    <!-- Favicon -->
    <link href="{{ asset('uploads/img/general/'.$general_site_image->favicon_image) }}" sizes="128x128" rel="shortcut icon" type="image/x-icon" />
    <link href="{{ asset('uploads/img/general/'.$general_site_image->favicon_image) }}" sizes="128x128" rel="shortcut icon" />
@else
    <!-- Favicon -->
    <link href="{{ asset('uploads/img/dummy/favicon.png') }}" sizes="128x128" rel="shortcut icon" type="image/x-icon" />
    <link href="{{ asset('uploads/img/dummy/favicon.png') }}" sizes="128x128" rel="shortcut icon" />
@endif

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
        
        <div style="position: relative; width: 100%; margin-bottom: 1rem;">
          <input type="password" id="loginPassword" name="password" placeholder="********" required style="padding-right: 44px; margin-bottom: 0;">
          <button type="button" onclick="togglePasswordVisibility('loginPassword', this)" title="Lihat/Sembunyikan Kata Sandi" aria-label="Lihat kata sandi" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: transparent; border: none; cursor: pointer; color: #64748b; padding: 4px; display: flex; align-items: center; justify-content: center; outline: none; transition: color 0.15s ease;">
            <!-- Eye Open Icon -->
            <svg class="eye-open-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
              <circle cx="12" cy="12" r="3"></circle>
            </svg>
            <!-- Eye Slash Icon (Hidden by default) -->
            <svg class="eye-slash-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
              <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
              <line x1="1" y1="1" x2="23" y2="23"></line>
            </svg>
          </button>
        </div>

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
        
        <div style="position: relative; width: 100%; margin-bottom: 1rem;">
          <input type="password" id="regPassword" name="password" placeholder="Tuliskan kata sandi yang kuat" required style="padding-right: 44px; margin-bottom: 0;">
          <button type="button" onclick="togglePasswordVisibility('regPassword', this)" title="Lihat/Sembunyikan Kata Sandi" aria-label="Lihat kata sandi" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: transparent; border: none; cursor: pointer; color: #64748b; padding: 4px; display: flex; align-items: center; justify-content: center; outline: none; transition: color 0.15s ease;">
            <svg class="eye-open-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
              <circle cx="12" cy="12" r="3"></circle>
            </svg>
            <svg class="eye-slash-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
              <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
              <line x1="1" y1="1" x2="23" y2="23"></line>
            </svg>
          </button>
        </div>

        <div style="position: relative; width: 100%; margin-bottom: 1rem;">
          <input type="password" id="regPasswordConfirm" name="password_confirmation" placeholder="Konfirmasi kembali kata sandi anda" required style="padding-right: 44px; margin-bottom: 0;">
          <button type="button" onclick="togglePasswordVisibility('regPasswordConfirm', this)" title="Lihat/Sembunyikan Kata Sandi" aria-label="Lihat kata sandi" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: transparent; border: none; cursor: pointer; color: #64748b; padding: 4px; display: flex; align-items: center; justify-content: center; outline: none; transition: color 0.15s ease;">
            <svg class="eye-open-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
              <circle cx="12" cy="12" r="3"></circle>
            </svg>
            <svg class="eye-slash-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
              <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
              <line x1="1" y1="1" x2="23" y2="23"></line>
            </svg>
          </button>
        </div>

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

  <script>
    function togglePasswordVisibility(inputId, btn) {
      const input = document.getElementById(inputId);
      if (!input) return;
      const eyeOpen = btn.querySelector('.eye-open-icon');
      const eyeSlash = btn.querySelector('.eye-slash-icon');
      
      if (input.type === 'password') {
        input.type = 'text';
        if (eyeOpen) eyeOpen.style.display = 'none';
        if (eyeSlash) eyeSlash.style.display = 'block';
        btn.setAttribute('aria-label', 'Sembunyikan kata sandi');
        btn.style.color = '#2d8aba';
      } else {
        input.type = 'password';
        if (eyeOpen) eyeOpen.style.display = 'block';
        if (eyeSlash) eyeSlash.style.display = 'none';
        btn.setAttribute('aria-label', 'Lihat kata sandi');
        btn.style.color = '#64748b';
      }
    }
  </script>
</body>
<!--// loginregis Js //-->
<script src="{{ asset('assets/frontend/js/loginregis.js') }}"></script>
</html>