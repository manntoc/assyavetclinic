<x-guest-layout>
    <div class="kreloses-auth-card">
        <!-- BRAND HEADER -->
        <div class="text-center mb-4">
            <div class="auth-brand-logo">
                <i class="bi bi-key-fill"></i>
            </div>
            <h1 class="auth-title mb-1">Lupa Kata Sandi</h1>
            <p class="auth-subtitle">Masukkan email Anda untuk menerima tautan atur ulang kata sandi.</p>
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
                    Gagal Mengirim Link:
                </div>
                <ul class="mb-0 ps-4 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- FORGOT PASSWORD FORM -->
        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <!-- EMAIL FIELD -->
            <div class="mb-4">
                <label for="email" class="form-label">Alamat Email <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="nama@klinik.com" required autofocus autocomplete="username">
                </div>
            </div>

            <!-- SUBMIT BUTTON -->
            <button type="submit" class="btn-kreloses-blue py-2 fs-6">
                <i class="bi bi-send-fill me-1"></i> Kirim Tautan Reset Kata Sandi
            </button>
        </form>

        <!-- FOOTER LINK TO LOGIN -->
        <div class="auth-footer">
            Kembali ke halaman 
            <a href="{{ route('login') }}" class="auth-link">Masuk Ke Sistem</a>
        </div>
    </div>
</x-guest-layout>

