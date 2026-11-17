<x-guest-layout class="wide">
    <div class="kreloses-auth-card">
        <!-- BRAND HEADER -->
        <div class="text-center mb-4">
            <div class="auth-brand-logo">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h1 class="auth-title mb-1">Atur Ulang Kata Sandi</h1>
            <p class="auth-subtitle">Masukkan kata sandi baru untuk akun Anda.</p>
        </div>

        <!-- ERROR VALIDATION ALERT -->
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-4 p-3" role="alert" style="background-color: #fef2f2; color: #991b1b; border-radius: 10px;">
                <div class="d-flex align-items-center mb-1 fw-bold small">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    Gagal Mengatur Ulang Kata Sandi:
                </div>
                <ul class="mb-0 ps-4 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- RESET PASSWORD FORM -->
        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <!-- EMAIL FIELD -->
            <div class="mb-3">
                <label for="email" class="form-label">Alamat Email <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
                </div>
            </div>

            <!-- PASSWORD FIELD -->
            <div class="mb-3">
                <label for="password" class="form-label">Kata Sandi Baru <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 8 karakter" required autocomplete="new-password">
                    <button class="password-toggle-btn" type="button" onclick="togglePassword('password', 'passwordIcon')" title="Lihat/Sembunyikan Password">
                        <i class="bi bi-eye-fill" id="passwordIcon"></i>
                    </button>
                </div>
            </div>

            <!-- CONFIRM PASSWORD FIELD -->
            <div class="mb-4">
                <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi Baru <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Ulangi kata sandi baru" required autocomplete="new-password">
                    <button class="password-toggle-btn" type="button" onclick="togglePassword('password_confirmation', 'confirmPasswordIcon')" title="Lihat/Sembunyikan Password">
                        <i class="bi bi-eye-fill" id="confirmPasswordIcon"></i>
                    </button>
                </div>
            </div>

            <!-- SUBMIT BUTTON -->
            <button type="submit" class="btn-kreloses-blue py-2 fs-6">
                <i class="bi bi-arrow-repeat me-1"></i> Simpan Kata Sandi Baru
            </button>
        </form>

        <!-- FOOTER LINK TO LOGIN -->
        <div class="auth-footer">
            Kembali ke 
            <a href="{{ route('login') }}" class="auth-link">Halaman Masuk</a>
        </div>
    </div>
</x-guest-layout>

