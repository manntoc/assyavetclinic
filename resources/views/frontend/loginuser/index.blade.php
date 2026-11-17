<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assyavetclinic - Login / Register</title>
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
      
      <form method="POST" action="{{ route('loginuser') }}">
        @csrf
        <input type="email" name="email" placeholder="Email" required>
        <input type="password"  name="password" placeholder="********">
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