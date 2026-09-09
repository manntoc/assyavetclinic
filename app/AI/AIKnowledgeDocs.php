<?php

namespace App\AI;

/**
 * AIKnowledgeDocs : Internal RAG document store for Assyavet VetClinic OS.
 *
 * Contains structured SOP, menu navigation, and feature flow documentation
 * for every module in the system. Injected into <system_manual> XML block
 * when QueryRewriterService classifies intent as RAG_DOCS.
 *
 * Usage:
 *   $content = AIKnowledgeDocs::getRelevantDocs($userQuery);
 *   // Returns concatenated doc strings matching the query keywords.
 *
 * Design:
 *   - Each doc is a static string returned by a private method.
 *   - getRelevantDocs() scores docs by keyword hits; returns top matches.
 *   - No external dependencies, no DB calls, no embeddings needed.
 *   - Update individual doc methods when system flow changes.
 */
class AIKnowledgeDocs
{
    /**
     * Keyword map: each entry maps doc method name → keywords that trigger it.
     * Order matters only for readability; scoring handles priority.
     */
    private static array $docKeywords = [
        'booking'       => ['booking', 'buat booking', 'tambah booking', 'daftar pasien', 'janji temu',
                            'appointment', 'kunjungan', 'antrean', 'antrian', 'queue', 'pekerjaan',
                            'walk-in', 'walkin', 'pendaftaran', 'daftar hewan'],
        'queue_stages'  => ['stage', 'alur', 'tahap', 'proses', 'mulai pemeriksaan', 'rawat inap',
                            'apotek', 'pembayaran', 'selesai', 'status booking', 'pindah stage',
                            'lanjut stage', 'ke apotek', 'proses bayar', 'konfirmasi lunas'],
        'owner_pet'     => ['owner', 'pemilik', 'hewan', 'tambah owner', 'daftar owner', 'data pemilik',
                            'pasien baru', 'hewan baru', 'tambah hewan', 'relasi owner', 'cari owner',
                            'edit owner', 'hapus owner', 'foto hewan'],
        'emr'           => ['emr', 'rekam medis', 'soap', 'subjective', 'objective', 'assessment', 'plan',
                            'tanda vital', 'suhu', 'denyut jantung', 'crt', 'diagnosa', 'anamnesis',
                            'pemeriksaan fisik', 'foto klinis', 'observasi', 'catatan medis'],
        'inventori'     => ['inventori', 'stok', 'obat', 'barang', 'tambah barang', 'tambah obat',
                            'update stok', 'edit barang', 'hapus barang', 'ketersediaan', 'farmasi',
                            'apotek', 'ambil obat', 'pengambilan', 'mutasi stok', 'jumlah stok'],
        'layanan'       => ['layanan', 'tarif', 'harga', 'jasa dokter', 'vaksin', 'grooming', 'operasi',
                            'diagnosa penunjang', 'transportasi', 'tambah layanan', 'jenis layanan',
                            'tipe layanan', 'biaya layanan', 'update layanan'],
        'estimasi'      => ['estimasi', 'estimasi biaya', 'nota estimasi', 'perkiraan biaya', 'rincian biaya',
                            'cetak estimasi', 'pdf estimasi', 'grand total estimasi', 'diskon estimasi'],
        'transaksi'     => ['transaksi', 'ambil obat', 'pengambilan obat', 'tambah transaksi',
                            'item transaksi', 'total transaksi', 'riwayat transaksi', 'log transaksi'],
        'laporan'       => ['laporan', 'omzet', 'pendapatan', 'laporan omzet', 'laporan keuangan',
                            'cetak laporan', 'pdf laporan', 'export laporan', 'rekap omzet',
                            'filter omzet', 'total omzet', 'grafik omzet'],
        'pembayaran'    => ['pembayaran', 'bayar', 'lunas', 'metode bayar', 'tunai', 'transfer',
                            'dp', 'down payment', 'uang muka', 'kasir', 'nota', 'cetak nota',
                            'invoice', 'diskon', 'pajak', 'total bayar'],
        'karyawan'      => ['karyawan', 'staff', 'dokter', 'tambah karyawan', 'edit karyawan',
                            'hapus karyawan', 'daftar karyawan', 'role', 'akses karyawan'],
        'rawat_inap'    => ['rawat inap', 'inap', 'opname', 'pet daily log', 'log harian', 'observasi harian',
                            'catatan harian', 'kondisi hewan rawat', 'update kondisi'],
        'user_akses'    => ['login', 'logout', 'password', 'akun', 'user', 'role', 'superadmin', 'admin',
                            'hak akses', 'permission', 'manajemen user', 'ganti password', 'profil'],
        'dashboard'     => ['dashboard', 'beranda', 'ringkasan', 'statistik', 'notifikasi', 'stok tipis',
                            'antrean aktif', 'total pasien', 'summary hari ini'],
        'nota_pdf'      => ['nota', 'cetak nota', 'pdf', 'nota pdf', 'print', 'cetak', 'nota selesai',
                            'download nota', 'laporan pdf'],
    ];

    /**
     * Get relevant doc(s) based on user query, scored by keyword hits.
     * Returns up to $limit top-scoring docs concatenated.
     */
    public static function getRelevantDocs(string $query, int $limit = 3): string
    {
        $lower  = strtolower($query);
        $scores = [];

        foreach (self::$docKeywords as $docKey => $keywords) {
            $score = 0;
            foreach ($keywords as $kw) {
                if (str_contains($lower, $kw)) {
                    // Longer keyword = more specific = higher weight
                    $score += strlen($kw) > 8 ? 3 : 1;
                }
            }
            if ($score > 0) {
                $scores[$docKey] = $score;
            }
        }

        if (empty($scores)) {
            // No specific match: return general navigation doc
            return self::docGeneralNavigation();
        }

        arsort($scores);
        $topKeys = array_slice(array_keys($scores), 0, $limit);

        $docs = [];
        foreach ($topKeys as $key) {
            $method = 'doc' . str_replace('_', '', ucwords($key, '_'));
            if (method_exists(self::class, $method)) {
                $docs[] = self::$method();
            }
        }

        return implode("\n\n---\n\n", array_filter($docs));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DOC METHODS : one per module/topic
    // ──────────────────────────────────────────────────────────────────────────

    private static function docBooking(): string
    {
        return <<<DOC
MODUL: Booking / Pendaftaran Pasien
Menu: Sidebar > Pekerjaan
Terakhir diperbarui: 2026-09-09

## Cara Membuat Booking Baru

1. Buka menu **Pekerjaan** di sidebar.
2. Klik tombol **"+ Tambah Booking"** (pojok kanan atas).
3. Isi form:
   - **Owner**: pilih atau ketik nama pemilik hewan (wajib).
   - **Hewan**: pilih hewan milik owner tersebut (wajib). Jika hewan belum ada, tambahkan dulu di menu Owner.
   - **Tanggal Kunjungan**: isi tanggal (format DD/MM/YYYY).
   - **Jam**: isi jam kunjungan.
   - **Tipe Kedatangan**: pilih Walk-in (langsung datang) atau Appointment (janji temu).
   - **Jenis Hewan**: terisi otomatis dari data hewan yang dipilih.
   - **Berat Hewan**: isi berat hewan saat ini (kg), opsional.
4. Klik **Simpan**. Booking akan muncul di daftar Pekerjaan dengan status "Belum Dikerjakan".

## Catatan Penting
- Kode booking (format: BOOK + 7 karakter) dibuat otomatis oleh sistem.
- Satu owner bisa memiliki beberapa booking untuk hewan yang berbeda.
- Booking yang sudah dibuat bisa diedit selama belum selesai.
- Untuk hapus booking, pastikan belum ada transaksi yang terkait.

## Cara Mencari / Filter Booking
- Gunakan kolom pencarian di halaman Pekerjaan (cari by nama owner, nama hewan, atau kode booking).
- Filter berdasarkan status (Belum Dikerjakan, Sedang Dikerjakan, dll.).
- Filter berdasarkan tanggal menggunakan kolom tanggal.
DOC;
    }

    private static function docQueueStages(): string
    {
        return <<<DOC
MODUL: Alur Klinis 6 Stage (Queue Flow)
Menu: Sidebar > Pekerjaan > [pilih booking]
Terakhir diperbarui: 2026-09-09

## Penjelasan 6 Stage Alur Pasien

| Stage | Nama         | Status di Sistem        | Cara Pindah Stage                         |
|-------|--------------|-------------------------|-------------------------------------------|
| 1     | Kedatangan   | Belum Dikerjakan        | Booking baru dibuat otomatis masuk stage 1 |
| 2     | Pemeriksaan  | Sedang Dikerjakan       | Klik tombol **"Mulai Pemeriksaan"**       |
| 3     | Rawat Inap   | Rawat Inap              | Klik tombol **"Masuk Rawat Inap"**        |
| 4     | Apotek       | Apotek                  | Klik tombol **"Ke Apotek"**               |
| 5     | Pembayaran   | Pembayaran              | Klik tombol **"Proses Bayar"**            |
| 6     | Selesai      | Telah Diselesaikan      | Kasir klik **"Konfirmasi Lunas"**         |

## Konten yang Tersedia per Stage

- **Stage 1 & 2**: Input EMR (SOAP, tanda vital, foto klinis), input layanan medis.
- **Stage 3 (Rawat Inap)**: Semua Stage 2 + tambah log observasi harian (PetDailyLog). Tidak bisa langsung ke Stage 5.
- **Stage 4 (Apotek)**: Input pengambilan obat dari inventori (mutasi stok otomatis berkurang).
- **Stage 5 (Pembayaran)**: Input DP, diskon, pajak, metode pembayaran, cetak nota sementara.
- **Stage 6 (Selesai)**: Readonly. Cetak nota final PDF, lihat riwayat lengkap.

## Aturan Stage
- Stage tidak bisa dilewati: harus urut 1 > 2 > 3 (opsional) > 4 > 5 > 6.
- Stage 3 (Rawat Inap) adalah opsional: pasien rawat jalan bisa langsung dari Stage 2 ke Stage 4.
- Setelah Stage 6 (Selesai), data tidak bisa diubah.
DOC;
    }

    private static function docOwnerPet(): string
    {
        return <<<DOC
MODUL: Manajemen Owner & Hewan Peliharaan
Menu: Sidebar > Owner
Terakhir diperbarui: 2026-09-09

## Cara Menambah Owner Baru

1. Buka menu **Owner** di sidebar.
2. Klik tombol **"+ Tambah Owner"**.
3. Isi form:
   - **Nama Owner**: nama lengkap pemilik (wajib).
   - **No. HP / Telepon**: nomor kontak (wajib).
   - **Alamat**: alamat lengkap (opsional).
   - **Email**: email pemilik (opsional).
4. Klik **Simpan**.

## Cara Menambah Hewan Milik Owner

1. Di halaman detail Owner, klik **"+ Tambah Hewan"**.
2. Isi form hewan:
   - **Nama Hewan**: nama panggilan hewan (wajib).
   - **Jenis Hewan**: pilih dari daftar (Kucing, Anjing, Kelinci, Burung, dll.) (wajib).
   - **Ras/Breed**: ras hewan (opsional).
   - **Tanggal Lahir / Umur**: untuk referensi klinis.
   - **Jenis Kelamin**: jantan/betina.
   - **Warna/Ciri-ciri**: deskripsi fisik.
   - **Foto Hewan**: upload foto (opsional).
3. Klik **Simpan**. Hewan otomatis terhubung ke owner tersebut.

## Aturan Relasi
- Satu owner bisa memiliki banyak hewan.
- Satu hewan hanya bisa dimiliki satu owner.
- Saat membuat booking, owner dan hewan harus sudah terdaftar di sistem.
- Untuk hapus owner, semua booking dan hewan terkait harus dihapus dulu.

## Cara Edit / Hapus Owner atau Hewan
- Klik ikon **edit (pensil)** di baris data yang ingin diubah.
- Klik ikon **hapus (tempat sampah)** untuk menghapus; akan muncul dialog konfirmasi.
DOC;
    }

    private static function docEmr(): string
    {
        return <<<DOC
MODUL: EMR (Rekam Medis Elektronik) & Tanda Vital
Menu: Pekerjaan > [pilih booking] > Tab EMR / Tanda Vital
Tersedia di: Stage 1, 2, 3 (readonly di Stage 6)
Terakhir diperbarui: 2026-09-09

## Format SOAP (Subjective, Objective, Assessment, Plan)

| Field        | Keterangan                                           |
|--------------|------------------------------------------------------|
| Subjective   | Keluhan utama dari pemilik hewan                     |
| Objective    | Hasil pemeriksaan fisik oleh dokter                  |
| Assessment   | Diagnosa / kesimpulan klinis                         |
| Plan         | Rencana tindak lanjut, terapi, atau resep            |

## Cara Input EMR

1. Buka detail booking (klik kode booking di halaman Pekerjaan).
2. Pastikan booking sudah di Stage 2 (Sedang Dikerjakan).
3. Klik tab **"Rekam Medis"** atau **"EMR"**.
4. Isi field Subjective, Objective, Assessment, Plan.
5. Klik **Simpan EMR**.

## Input Tanda Vital

Di halaman yang sama, scroll ke bagian Tanda Vital:
- **Suhu Tubuh**: dalam derajat Celsius (°C). Normal kucing: 38-39°C.
- **Denyut Jantung**: dalam bpm. Normal kucing: 120-140 bpm.
- **CRT (Capillary Refill Time)**: < 2 detik = normal.
- **Berat Hewan**: berat saat pemeriksaan (kg).
Klik **Simpan Tanda Vital**.

## Upload Foto Klinis
- Klik **"Upload Foto"** di tab Foto Klinis.
- Format: JPG/PNG, maks 5MB per file.
- Bisa upload lebih dari satu foto.

## Catatan
- EMR hanya bisa diedit sampai booking mencapai Stage 6 (Selesai).
- EMR bisa dilihat (readonly) di Stage 6 untuk referensi riwayat.
DOC;
    }

    private static function docInventori(): string
    {
        return <<<DOC
MODUL: Inventori & Stok Obat/Barang
Menu: Sidebar > Inventori (atau Apotek)
Terakhir diperbarui: 2026-09-09

## Cara Menambah Barang / Obat Baru

1. Buka menu **Inventori** di sidebar.
2. Klik **"+ Tambah Barang"**.
3. Isi form:
   - **Nama Barang/Obat**: nama lengkap (wajib).
   - **Harga**: harga satuan dalam Rupiah (wajib).
   - **Jumlah/Stok Awal**: stok awal tersedia (wajib).
   - **Tanggal Pembuatan / Kedaluwarsa**: (opsional, untuk referensi).
4. Klik **Simpan**.

## Status Stok
| Stok        | Status             |
|-------------|--------------------|
| > 10 unit   | Tersedia (hijau)   |
| 1-10 unit   | Stok Tipis (kuning)|
| 0 unit      | Habis (merah)      |

## Cara Update Stok (Penambahan Manual)
1. Klik ikon **edit** pada barang yang ingin diupdate.
2. Ubah nilai jumlah stok.
3. Klik **Simpan**.
- Catatan: pengurangan stok terjadi otomatis saat obat diambil via transaksi di booking Stage 4 (Apotek).

## Cara Mengambil Obat untuk Booking (Stage 4 - Apotek)
1. Buka detail booking yang sudah di Stage 4.
2. Di bagian **Transaksi / Pengambilan Obat**, klik **"+ Tambah Item"**.
3. Pilih barang/obat dari dropdown inventori.
4. Isi jumlah yang diambil.
5. Sistem otomatis menghitung total (harga x jumlah) dan mengurangi stok inventori.
6. Klik **Simpan**.

## Catatan Penting
- Stok tidak bisa negatif: sistem akan memblokir jika jumlah pengambilan melebihi stok tersedia.
- Estimasi Biaya (nota estimasi) TIDAK mengurangi stok. Hanya transaksi di Stage 4 yang mengurangi stok.
- Untuk hapus item transaksi, stok akan dikembalikan (rollback).
DOC;
    }

    private static function docLayanan(): string
    {
        return <<<DOC
MODUL: Layanan Medis & Tarif
Menu: Sidebar > Layanan
Terakhir diperbarui: 2026-09-09

## Jenis Layanan yang Tersedia

| Kode Jenis           | Nama Tampilan         | Keterangan                             |
|----------------------|-----------------------|----------------------------------------|
| jasa_dokter          | Jasa Dokter           | Biaya konsultasi/pemeriksaan dokter    |
| vaksin               | Vaksinasi             | Layanan vaksin (rabies, dll.)          |
| grooming             | Grooming              | Mandi, potong kuku, dll.               |
| operasi              | Operasi / Tindakan    | Operasi kecil atau tindakan medis      |
| diagnosa_penunjang   | Diagnosa Penunjang    | Lab, rontgen, USG, dll.                |
| transportasi         | Transportasi          | Antar jemput pasien                    |
| lainnya              | Lainnya               | Layanan di luar kategori di atas       |

## Cara Menambah Layanan Baru ke Master Data

1. Buka menu **Layanan** di sidebar.
2. Klik **"+ Tambah Layanan"**.
3. Isi:
   - **Nama Layanan**: nama spesifik (contoh: "Vaksin Rabies 1 Dosis").
   - **Jenis**: pilih dari dropdown jenis di atas.
   - **Harga**: harga standar dalam Rupiah.
4. Klik **Simpan**.

## Cara Menambah Layanan ke Booking

1. Buka detail booking (Stage 1 atau 2).
2. Di bagian **Layanan Medis**, klik **"+ Tambah Layanan"**.
3. Pilih layanan dari dropdown (diambil dari master data Layanan).
4. Isi kuantitas jika diperlukan.
5. Sistem otomatis menghitung subtotal.
6. Klik **Simpan**.

- Satu booking bisa memiliki banyak layanan dengan jenis berbeda.
- Harga layanan di booking diambil dari master data, bisa diedit per-booking jika perlu.
DOC;
    }

    private static function docEstimasi(): string
    {
        return <<<DOC
MODUL: Estimasi Biaya (Nota Sementara)
Menu: Sidebar > Estimasi Biaya
Terakhir diperbarui: 2026-09-09

## Penjelasan Estimasi Biaya
Estimasi Biaya adalah perkiraan rincian biaya yang dibuat SEBELUM pembayaran final.
- Bisa dibuat untuk booking yang masih berjalan (booking_id opsional).
- TIDAK memotong stok inventori (berbeda dengan transaksi di Stage 4).
- Bisa dicetak sebagai PDF untuk dikonsultasikan ke pemilik hewan.

## Cara Membuat Estimasi Biaya

1. Buka menu **Estimasi Biaya** di sidebar.
2. Klik **"+ Tambah Estimasi"**.
3. Isi header:
   - **Booking**: pilih booking terkait (opsional).
   - **Owner**: nama pemilik.
   - **Tanggal**: tanggal estimasi.
4. Tambah item layanan:
   - Klik **"+ Tambah Layanan"**, pilih layanan, isi kuantitas dan harga.
5. Tambah item obat (opsional):
   - Klik **"+ Tambah Obat"**, pilih dari inventori, isi kuantitas dan harga.
6. Isi:
   - **Diskon**: nominal diskon (Rp), default 0.
   - **Pajak**: nominal pajak (Rp), default 0.
7. Grand Total dihitung otomatis: `subtotal_layanan + subtotal_obat - diskon + pajak`.
8. Klik **Simpan**.

## Cara Cetak PDF Estimasi
- Di halaman daftar Estimasi Biaya, klik ikon **PDF / Cetak** di baris estimasi yang diinginkan.
- PDF terbuka di tab baru, siap dicetak atau disimpan.

## Cara Hapus Estimasi
- Klik ikon **hapus** di baris estimasi.
- Muncul dialog konfirmasi SweetAlert2. Klik **"Ya, Hapus"** untuk melanjutkan.
- Estimasi yang dihapus tidak bisa dipulihkan.
DOC;
    }

    private static function docTransaksi(): string
    {
        return <<<DOC
MODUL: Transaksi Pengambilan Obat
Menu: Pekerjaan > [booking Stage 4] > Tab Apotek/Transaksi
Terakhir diperbarui: 2026-09-09

## Penjelasan Transaksi
Transaksi adalah pencatatan pengambilan obat/barang NYATA dari inventori untuk booking tertentu.
- Dibuat di Stage 4 (Apotek).
- Mengurangi stok inventori secara otomatis (real-time).
- Menjadi bagian dari total billing akhir.

## Cara Menambah Item Transaksi

1. Pastikan booking di Stage 4 (Apotek). Klik **"Ke Apotek"** di detail booking jika belum.
2. Di tab **Apotek**, klik **"+ Tambah Item Obat"**.
3. Pilih barang dari dropdown inventori.
   - Stok tersedia ditampilkan di sebelah nama barang.
4. Isi jumlah yang diambil.
5. Harga dan total otomatis terisi.
6. Klik **Simpan**.

## Cara Hapus Item Transaksi
- Klik ikon **hapus** di baris item transaksi.
- Stok akan dikembalikan ke inventori otomatis.

## Melihat Log Transaksi Keseluruhan
- Buka menu **Laporan** > **Log Transaksi Obat** untuk melihat semua riwayat pengambilan obat lintas booking.
DOC;
    }

    private static function docLaporan(): string
    {
        return <<<DOC
MODUL: Laporan Keuangan & Omzet
Menu: Sidebar > Laporan
Terakhir diperbarui: 2026-09-09

## Sumber Data Omzet
Omzet dihitung dari booking dengan status **"Telah Diselesaikan"** (Stage 6), berdasarkan field `total` di tabel booking.

## Cara Melihat Laporan Omzet

1. Buka menu **Laporan** di sidebar.
2. Pilih filter periode:
   - **Hari Ini**: otomatis menampilkan omzet hari ini.
   - **Bulan Ini**: omzet bulan berjalan.
   - **Bulan Lalu**: omzet bulan sebelumnya.
   - **Custom**: pilih rentang tanggal sendiri (dari - sampai).
3. Klik **Tampilkan** atau **Filter**.
4. Laporan menampilkan:
   - Total omzet periode.
   - Daftar booking yang sudah selesai dalam periode tersebut.
   - Rincian per booking: kode, owner, hewan, tanggal, total.

## Cara Cetak / Export PDF Laporan Omzet
- Klik tombol **"Cetak PDF"** atau **"Export PDF"** di halaman laporan.
- PDF akan terbuka di tab baru.
- Rute langsung: `/laporan-omzet/pdf` (dengan parameter filter tanggal).

## Catatan
- Booking yang masih dalam proses (Stage 1-5) TIDAK masuk hitungan omzet.
- Total omzet = jumlah field `total` dari semua booking selesai dalam periode.
- Diskon dan pajak sudah termasuk dalam field `total`.
DOC;
    }

    private static function docPembayaran(): string
    {
        return <<<DOC
MODUL: Pembayaran & Billing
Menu: Pekerjaan > [booking Stage 5] > Tab Pembayaran
Terakhir diperbarui: 2026-09-09

## Alur Pembayaran

1. Setelah Stage 4 (Apotek) selesai, klik **"Proses Bayar"** untuk pindah ke Stage 5.
2. Di tab Pembayaran, isi:
   - **Down Payment (DP)**: uang muka jika ada (Rp), default 0.
   - **Diskon**: nominal diskon (Rp), default 0.
   - **Pajak**: nominal pajak (Rp), default 0.
   - **Metode Pembayaran**: Tunai / Transfer / QRIS / lainnya.
   - **Tanggal Kontrol**: jadwal kunjungan berikutnya (opsional).
3. **Total Akhir** dihitung otomatis: subtotal layanan + subtotal obat + pajak - diskon.
4. Klik **Simpan Pembayaran**.
5. Setelah pembayaran diisi, kasir klik **"Konfirmasi Lunas"** untuk memindahkan ke Stage 6 (Selesai).

## Cetak Nota / Invoice
- Klik **"Cetak Nota"** di halaman detail booking (tersedia mulai Stage 5).
- PDF nota mencantumkan: kode booking, owner, hewan, daftar layanan, daftar obat, total, metode bayar.
- Nota final PDF tersedia setelah Stage 6.

## Catatan
- Setelah Stage 6, data pembayaran tidak bisa diubah.
- Untuk koreksi, hubungi admin/superadmin untuk rollback manual.
DOC;
    }

    private static function docKaryawan(): string
    {
        return <<<DOC
MODUL: Manajemen Karyawan / Staff
Menu: Sidebar > Karyawan
Terakhir diperbarui: 2026-09-09

## Cara Menambah Karyawan Baru

1. Buka menu **Karyawan** di sidebar.
2. Klik **"+ Tambah Karyawan"**.
3. Isi form:
   - **Nama Lengkap**: nama karyawan (wajib).
   - **Jabatan / Role**: dokter, perawat, resepsionis, kasir, dll.
   - **No. HP**: nomor kontak.
   - **Email**: email (opsional).
4. Klik **Simpan**.

## Role dan Hak Akses Sistem

| Role        | Hak Akses Utama                                               |
|-------------|--------------------------------------------------------------|
| superadmin  | Semua fitur: booking, owner, inventori, laporan, manajemen user & role |
| admin       | Booking, owner, inventori, layanan, karyawan, laporan, estimasi |
| karyawan    | Lihat & update booking, update stage, input EMR, log observasi |

## Cara Edit / Hapus Karyawan
- Klik ikon **edit** di baris karyawan untuk mengubah data.
- Klik ikon **hapus** untuk menghapus; konfirmasi akan muncul.

## Manajemen Akun Login
- Pembuatan akun login (username/password) untuk karyawan dilakukan di menu **Manajemen User** (hanya superadmin).
- Karyawan bisa ganti password sendiri di halaman Profil setelah login.
DOC;
    }

    private static function docRawatInap(): string
    {
        return <<<DOC
MODUL: Rawat Inap & PetDailyLog (Observasi Harian)
Menu: Pekerjaan > [booking Stage 3] > Tab Rawat Inap
Tersedia di: Stage 3 saja
Terakhir diperbarui: 2026-09-09

## Penjelasan Rawat Inap
Rawat Inap (Stage 3) adalah kondisi di mana hewan menginap di klinik untuk perawatan intensif.
- PetDailyLog adalah catatan observasi kondisi hewan setiap hari selama rawat inap.
- Setiap hari bisa ditambahkan satu atau lebih log kondisi.

## Cara Mengaktifkan Stage Rawat Inap
1. Booking harus di Stage 2 (Sedang Dikerjakan).
2. Klik tombol **"Masuk Rawat Inap"**.
3. Booking pindah ke Stage 3.

## Cara Menambah Log Observasi Harian (PetDailyLog)
1. Buka detail booking di Stage 3.
2. Klik tab **"Rawat Inap"** atau **"Observasi Harian"**.
3. Klik **"+ Tambah Catatan Harian"**.
4. Isi:
   - **Tanggal Observasi**: tanggal catatan.
   - **Kondisi Umum**: deskripsi kondisi hewan hari ini.
   - **Suhu, Denyut Jantung** (opsional): update tanda vital harian.
   - **Tindakan / Terapi Hari Ini**: apa yang dilakukan.
   - **Catatan Tambahan**: info lain yang perlu dicatat.
5. Klik **Simpan**.

## Cara Keluar dari Rawat Inap
- Setelah hewan siap dipulangkan, klik **"Ke Apotek"** untuk pindah ke Stage 4.
- Semua log harian tetap tersimpan dan bisa dilihat di riwayat booking.
DOC;
    }

    private static function docUserAkses(): string
    {
        return <<<DOC
MODUL: Akun Login & Manajemen User
Menu: Sidebar > Manajemen User (superadmin saja) / Profil
Terakhir diperbarui: 2026-09-09

## Cara Login ke Sistem
1. Buka URL sistem Assyavet di browser.
2. Masukkan **Email** dan **Password** akun.
3. Klik **Login**.
4. Jika lupa password, hubungi superadmin untuk reset.

## Role yang Tersedia
| Role        | Akses                                                        |
|-------------|--------------------------------------------------------------|
| superadmin  | Semua menu + kelola user, role, dan data master              |
| admin       | Semua operasional klinik kecuali manajemen user              |
| karyawan    | Operasional terbatas: booking, EMR, stage update             |

## Cara Membuat Akun User Baru (Superadmin)
1. Login sebagai superadmin.
2. Buka menu **Manajemen User**.
3. Klik **"+ Tambah User"**.
4. Isi nama, email, password, dan pilih role.
5. Klik **Simpan**.

## Cara Ganti Password (Semua User)
1. Klik nama profil di pojok kanan atas navbar.
2. Pilih **Profil** atau **Pengaturan**.
3. Isi password lama, password baru, konfirmasi password baru.
4. Klik **Simpan**.

## Cara Logout
- Klik nama profil di pojok kanan atas.
- Pilih **Logout**.
DOC;
    }

    private static function docDashboard(): string
    {
        return <<<DOC
MODUL: Dashboard / Beranda
Menu: Sidebar > Dashboard (halaman utama setelah login)
Terakhir diperbarui: 2026-09-09

## Informasi di Dashboard

Dashboard menampilkan ringkasan operasional klinik hari ini:

- **Antrean Aktif**: jumlah booking yang sedang dalam proses (Stage 1-5).
- **Booking Hari Ini**: total booking terdaftar untuk hari ini.
- **Booking Selesai Hari Ini**: booking yang sudah mencapai Stage 6.
- **Omzet Hari Ini**: total pendapatan dari booking selesai hari ini.
- **Stok Tipis / Habis**: notifikasi otomatis untuk barang dengan stok <= 10 atau 0.
- **Grafik Omzet**: grafik omzet mingguan/bulanan (jika tersedia).

## Navigasi dari Dashboard
- Klik angka antrean aktif untuk langsung ke halaman Pekerjaan yang difilter.
- Klik notifikasi stok tipis untuk langsung ke menu Inventori.

## Catatan
- Data dashboard diperbarui real-time.
- Omzet hanya menghitung booking dengan status "Telah Diselesaikan".
DOC;
    }

    private static function docNotaPdf(): string
    {
        return <<<DOC
MODUL: Cetak Nota & PDF
Menu: Pekerjaan > [booking] > Tombol Cetak Nota / Laporan > Cetak PDF
Terakhir diperbarui: 2026-09-09

## Jenis PDF yang Tersedia

| Dokumen             | Kapan Tersedia         | Rute / Cara Akses                        |
|---------------------|------------------------|------------------------------------------|
| Nota Booking Final  | Stage 6 (Selesai)      | Detail booking > klik "Cetak Nota PDF"   |
| Nota Sementara      | Stage 5 (Pembayaran)   | Detail booking > klik "Cetak Nota"       |
| Nota Estimasi Biaya | Kapan saja             | Menu Estimasi > klik ikon PDF            |
| Laporan Omzet PDF   | Kapan saja             | Menu Laporan > klik "Cetak PDF"          |

## Cara Cetak Nota Booking

1. Buka detail booking (pastikan minimal Stage 5 untuk nota sementara, atau Stage 6 untuk nota final).
2. Klik tombol **"Cetak Nota"** atau **"Print Invoice"**.
3. PDF terbuka di tab baru.
4. Gunakan Ctrl+P (atau Cmd+P di Mac) untuk mencetak, atau klik ikon download untuk menyimpan.

## Isi Nota PDF
- Kode booking dan tanggal.
- Data owner dan hewan.
- Daftar layanan medis dengan harga.
- Daftar pengambilan obat/transaksi.
- Subtotal layanan, subtotal obat, diskon, pajak.
- **Total akhir** dan metode pembayaran.
- Tanda tangan (opsional, sesuai template klinik).
DOC;
    }

    private static function docGeneralNavigation(): string
    {
        return <<<DOC
NAVIGASI UMUM SISTEM ASSYAVET VETCLINIC OS
Terakhir diperbarui: 2026-09-09

## Struktur Menu Sidebar

| Menu          | Sub-Menu / Fungsi Utama                                        |
|---------------|----------------------------------------------------------------|
| Dashboard     | Ringkasan antrean, omzet hari ini, stok tipis                  |
| Pekerjaan     | Daftar booking, alur stage klinis, EMR, transaksi, pembayaran  |
| Owner         | Manajemen pemilik hewan dan data hewan peliharaan              |
| Inventori     | Stok obat dan barang, tambah/edit/hapus, mutasi stok           |
| Layanan       | Master data tarif dan jenis layanan medis                      |
| Estimasi Biaya| Buat nota estimasi / perkiraan biaya untuk owner               |
| Laporan       | Laporan omzet, log transaksi obat, export PDF                  |
| Karyawan      | Data dokter dan staf klinik                                    |
| Profil        | Pengaturan akun dan ganti password (semua user)                |
| Manajemen User| Tambah/edit/hapus akun login (superadmin saja)                 |

## Cara Menggunakan Sistem Secara Umum

Alur kerja standar per kunjungan pasien:
1. **Daftarkan Owner & Hewan** (jika baru) > menu Owner.
2. **Buat Booking** > menu Pekerjaan > "+ Tambah Booking".
3. **Jalankan Stage Klinis**: Kedatangan > Pemeriksaan > (Rawat Inap opsional) > Apotek > Pembayaran > Selesai.
4. **Input EMR** (SOAP + tanda vital) di Stage 2.
5. **Input Transaksi Obat** di Stage 4.
6. **Proses Bayar** di Stage 5, konfirmasi lunas.
7. **Cetak Nota PDF** setelah Stage 6.

Untuk pertanyaan lebih spesifik, tanyakan modul yang dimaksud (contoh: "cara buat booking", "cara cek stok obat", "cara cetak laporan omzet").
DOC;
    }
}
