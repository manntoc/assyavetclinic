@extends('layouts.app')

@section('title', 'Kebijakan Privasi - Assyavet Clinic')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="mb-4">Kebijakan Privasi</h1>
            <p class="text-muted">Terakhir diperbarui: {{ date('d F Y') }}</p>

            <h5 class="mt-4">1. Informasi yang Kami Kumpulkan</h5>
            <p>Kami mengumpulkan informasi yang Anda berikan secara langsung kepada kami, seperti nama, nomor telepon, dan informasi hewan peliharaan Anda saat melakukan reservasi atau menghubungi klinik.</p>

            <h5 class="mt-4">2. Penggunaan Informasi</h5>
            <p>Informasi yang dikumpulkan digunakan untuk keperluan layanan medis hewan, penjadwalan kunjungan, dan komunikasi terkait perawatan hewan peliharaan Anda.</p>

            <h5 class="mt-4">3. Keamanan Data</h5>
            <p>Kami berkomitmen untuk melindungi privasi dan keamanan data pribadi Anda sesuai dengan peraturan perlindungan data yang berlaku di Indonesia.</p>

            <h5 class="mt-4">4. Berbagi Informasi</h5>
            <p>Kami tidak menjual, menyewakan, atau membagikan informasi pribadi Anda kepada pihak ketiga tanpa izin Anda, kecuali diwajibkan oleh hukum.</p>

            <h5 class="mt-4">5. Hubungi Kami</h5>
            <p>Jika Anda memiliki pertanyaan tentang kebijakan privasi ini, silakan hubungi kami melalui halaman kontak atau kunjungi klinik kami langsung.</p>
        </div>
    </div>
</div>
@endsection
