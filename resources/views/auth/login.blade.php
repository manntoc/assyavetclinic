<x-guest-layout>
    <div class="kreloses-auth-card">
        <!-- BRAND HEADER -->
        <div class="text-center mb-4">
            <div class="auth-brand-logo">
                <i class="bi bi-hospital-fill"></i>
            </div>
            <h1 class="auth-title mb-1">VetClinic OS</h1>
            <p class="auth-subtitle">Sistem Informasi Manajemen Klinik Hewan</p>
        </div>

        <!-- STATUS SESSION NOTIFICATION -->
        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-4" role="alert" style="background-color: #d1fae5; color: #065f46; border-radius: 10px;">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div class="small fw-medium">{{ session('status') }}</div>
            </div>
        @endif

        <!-- ERROR VALIDATION ALERT -->
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-4 p-3" role="alert" style="background-color: #fef2f2; color: #991b1b; border-radius: 10px;">
                <div class="d-flex align-items-center mb-1 fw-bold small">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    Gagal Masuk:
                </div>
                <ul class="mb-0 ps-4 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- LOGIN FORM -->
        <form method="POST" action="{{ Route::has('login') ? route('login') : route('loginuser') }}">
            @csrf

            <!-- EMAIL FIELD -->
            <div class="mb-3">
                <label for="email" class="form-label">Alamat Email <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="nama@klinik.com" required autofocus autocomplete="username">
                </div>
            </div>

            <!-- PASSWORD FIELD -->
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="password" class="form-label mb-0">Kata Sandi <span class="text-danger">*</span></label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="auth-link small">Lupa Kata Sandi?</a>
                    @endif
                </div>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" required autocomplete="current-password">
                    <button class="password-toggle-btn" type="button" onclick="togglePassword('password', 'passwordIcon')" title="Lihat/Sembunyikan Password">
                        <i class="bi bi-eye-fill" id="passwordIcon"></i>
                    </button>
                </div>
            </div>

            <!-- REMEMBER ME CHECKBOX -->
            <div class="mb-4 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label small text-secondary fw-medium" for="remember">
                    Ingat saya di perangkat ini
                </label>
            </div>

            <!-- SUBMIT BUTTON -->
            <button type="submit" class="btn-kreloses-blue py-2 fs-6">
                <i class="bi bi-box-arrow-in-right fs-5 me-1"></i> Masuk ke Sistem
            </button>
        </form>

        <!-- FOOTER LINK TO REGISTER -->
        <div class="auth-footer">
            Belum memiliki akun pegawai? 
            <a href="{{ route('register') }}" class="auth-link">Daftar Sekarang</a>
        </div>
    </div>
</x-guest-layout>

