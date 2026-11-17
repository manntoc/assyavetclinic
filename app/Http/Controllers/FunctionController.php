<?php

namespace App\Http\Controllers;

use App\Models\booking;
use App\Models\Inventori;
use App\Models\layanan;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\Owner;
use App\Models\OwnerPet;
use App\Models\PetDailyLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;
use Illuminate\Support\Str;

class FunctionController extends Controller
{
    public function createbook($ownerId, $petId)
    {
        $owner = Owner::findOrFail($ownerId);

        $pet = OwnerPet::where('id', $petId)
                        ->where('owner_id', $ownerId)
                        ->firstOrFail();

        $karyawan = User::whereIn('role', ['karyawan', 'admin', 'superadmin'])->get();
        $layananDokter = layanan::where('jenis', 'Jasa Dokter')->get();
        if ($layananDokter->isEmpty()) {
            $layananDokter = layanan::all();
        }

        return view('booking', compact('owner', 'pet', 'karyawan', 'layananDokter'));
    }

    private function generateKodeBooking()
    {
        do {
            $kode = 'BOOK' . strtoupper(Str::random(7));
        } while (Booking::where('kode_booking', $kode)->exists());

        return $kode;
    }

    // Fungsi tambah booking 
    public function storebook(Request $request)
    {
        try {

            if (!auth()->check()) {
                return redirect()->route('login')
                    ->with('error', 'Mohon login terlebih dahulu.');
            }

            $request->validate([
                'owner_id'      => 'required|exists:owners,id',
                'owner_pet_id'  => 'required|exists:owner_pets,id',
                'nama'          => 'required|string|max:255',
                'namahewan'     => 'required|string|max:255',
                'jenishewan'    => 'required|string|max:255',
                'berathewan'    => 'required|string|max:255',
                'alamat'        => 'required|string',
                'telpon'        => 'required|string|max:20',
                'tanggal'       => 'required|date',
            ]);

            Booking::create([
                'owner_id'        => $request->owner_id,
                'owner_pet_id'    => $request->owner_pet_id,
                'kode_booking'    => $this->generateKodeBooking(),
                'tipe_kedatangan' => $request->tipe_kedatangan ?? 'Walk-in',
                'nama'            => $request->nama,
                'nama_hewan'      => $request->namahewan,
                'berat_hewan'     => $request->berathewan,
                'jenis_hewan'     => $request->jenishewan,
                'alamat'          => $request->alamat,
                'telpon'          => $request->telpon,
                'tanggal'         => Carbon::parse($request->tanggal),
                'jam'             => $request->jam ?? '09:00:00',
                'keluhan'         => $request->keluhan ?? 'Pemeriksaan / Janji Temu Pasien',
                'karyawan1'       => $request->karyawan1 ?? 'Belum Ditentukan',
                'jasa_dokter'     => $request->jasa_dokter ?? 'Jasa Dokter Konsultasi',
                'jasa_dokter_kuantitas' => 1,
                'jasa_dokter_harga'     => $request->jasa_dokter_harga ?? 50000,
                'status'          => $request->status ?? 'belum dikerjakan',
                'suhu_tubuh'      => $request->suhu_tubuh ?? null,
                'denyut_jantung'  => $request->denyut_jantung ?? null,
                'crt'             => $request->crt ?? null,
            ]);

            return redirect()->route('booking-uncompleted')
                ->with('success', 'Booking & Janji Temu baru berhasil ditambahkan.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->route('booking-uncompleted')
                ->with('error', 'Terjadi kesalahan pada sistem: ' . $e->getMessage());
        }
    }

    public function storeinventori(Request $request)
    {
        try {
            $request->validate([
                'nama_barang' => 'required|string|max:255',
                'tanggal_pembuatan' => 'required|date',
                'harga' => 'required|integer',
                'jumlah' => 'required|integer',
            ]);
            Inventori::create([
                'nama_barang' => $request->nama_barang,
                'tanggal_pembuatan' => Carbon::parse($request->tanggal_pembuatan),
                'harga' => $request->harga,
                'jumlah' => $request->jumlah,
            ]);
            return redirect()->route('inventori')->with('success', 'Barang berhasil ditambahkan di Inventori');
        } catch (\Illuminate\Validation\ValidationException | \Exception $e) {
            return redirect()->route('inventori')->with('error', 'Mohon maaf barang gagal ditambahkan di Inventori');
        }
    }

    // Fungsi tambah akun karyawan
   public function storeakunkaryawan(Request $request)
    {
        // Validasi data input
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        try {
            // Cek apakah email sudah digunakan
            if (User::where('email', $request->input('email'))->exists()) {
                return redirect()->back()->withErrors(['email' => 'Email sudah digunakan, silakan gunakan email lain.']);
            }

            // Buat user baru
            $user = new User();
            $user->name = $request->input('nama');
            $user->email = $request->input('email');
            $user->password = Hash::make($request->input('password'));
            $user->role = $request->input('role'); // Set role ke 'karyawan'
            $user->save();

            // Jika berhasil, redirect dengan pesan sukses
            return redirect()->route('karyawan')->with('success', 'Akun karyawan berhasil dibuat.');
        } catch (QueryException $e) {
            // Tangani kesalahan saat menyimpan data ke database
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.']);
        } catch (\Exception $e) {
            // Tangani kesalahan umum lainnya
            return redirect()->back()->withErrors(['error' => 'Pembuatan akun gagal. Silakan coba lagi.']);
        }
    }

    //fungsi tambah layanan
    public function storelayanan(Request $request)
    {
        try {
            $request->validate([
                'nama' => 'required|string|max:255',
                'jenis' => 'required|string|max:255',
                'harga' => 'required|integer',
            ]);
            layanan::create([
                'nama' => $request->nama,
                'jenis' => $request->jenis,
                'harga' => $request->harga,
            ]);
            return redirect()->route('layanan')->with('success', 'Layanan berhasil ditambahkan');
        } catch (\Illuminate\Validation\ValidationException | \Exception $e) {
            return redirect()->route('layanan')->with('error', 'Layanan gagal ditambahkan');
        }
    }

    //fungsi tambah pengambilan barang 
    public function storetransaksi(Request $request)
    {
        try {
            $request->validate([
                'booking_id' => 'required|exists:bookings,id',
                'inventori_id' => 'required|exists:inventoris,id',
                'jumlah' => 'required|integer|min:1',
                'total' => 'required|numeric|min:0',
            ]);

            $inventori = Inventori::findOrFail($request->inventori_id);
            if ($inventori->jumlah < $request->jumlah) {
                return back()->with('error', 'Mohon Maaf, stok barang "' . $inventori->nama_barang . '" tidak mencukupi (Stok sisa: ' . $inventori->jumlah . ')');
            }

            // Deduct stock
            $inventori->jumlah -= $request->jumlah;
            $inventori->save();

            // Calculate total if not set
            $totalBiaya = $request->filled('total') ? $request->total : ($inventori->harga * $request->jumlah);

            // Save new transaction
            Transaksi::create([
                'booking_id'   => $request->booking_id,
                'inventori_id' => $request->inventori_id,
                'jumlah'       => $request->jumlah,
                'total'        => $totalBiaya,
            ]);

            return redirect()->back()->with('success', 'Obat/barang ' . $inventori->nama_barang . ' berhasil ditambahkan ke pasien & stok telah diperbarui.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Store Transaksi Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses penambahan obat: ' . $e->getMessage());
        }
    }

    //fungsi update ambil barang
    public function updatetransaksi(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'inventori_id' => 'required|exists:inventoris,id',
            'penambahan' => 'nullable|integer|min:0',
            'pengurangan' => 'nullable|integer|min:0',
            'totalpenambahan' => 'nullable|numeric|min:0',
            'totalpengurangan' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $inventori = Inventori::find($request->inventori_id);
            $transaksi = Transaksi::where('booking_id', $request->booking_id)
                ->where('inventori_id', $request->inventori_id)
                ->first();

            if (!$inventori || !$transaksi) {
                return back()->withErrors(['message' => 'Inventori atau Transaksi tidak ditemukan']);
            }

            $jumlah_sebelumnya = $transaksi->jumlah;
            $penambahan = $request->penambahan ?? 0;
            $pengurangan = $request->pengurangan ?? 0;

            // Hitung jumlah baru
            $jumlah_baru = $jumlah_sebelumnya + $penambahan - $pengurangan;

            // Total penambahan dan pengurangan dari input
            $total_penambahan = $request->totalpenambahan ?? 0;
            $total_pengurangan = $request->totalpengurangan ?? 0;

            // Hitung total baru
            $total = $transaksi->total + $total_penambahan - $total_pengurangan;

            // Update stok inventori
            if ($penambahan > 0 && $inventori->jumlah < $penambahan) {
                return back()->withErrors(['jumlah' => 'Stok tidak mencukupi untuk penambahan']);
            }

            if ($penambahan > 0) {
                $inventori->jumlah -= $penambahan;
            }
            if ($pengurangan > 0) {
                $inventori->jumlah += $pengurangan;
            }

            $inventori->save();

            $transaksi->update([
                'jumlah' => $jumlah_baru,
                'total' => $total,
            ]);

            DB::commit();

            return redirect()->route('manajemen-stok')->with('success', 'Transaksi diperbarui dan stok disesuaikan dengan sukses');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['message' => 'Kesalahan saat memperbarui transaksi: ' . $e->getMessage()]);
        }
    }


    //fungsi update akun karyawan
    public function updateakunkaryawan(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        try {
            $user = User::findOrFail($id);
            $user->name = $validatedData['nama'];
            $user->email = $validatedData['email'];
            $user->password = Hash::make($validatedData['password']);
            $user->save();

            return redirect()->route('karyawan')->with('success', 'Data karyawan berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->route('karyawan')->with('error', 'Mohon maaf pastikan semua input telah kamu isi ya');
        }
    }

    //fungsi update barang inventori 
    public function updateinventori(Request $request, $id)
    {
        try {
            $request->validate([
                'nama_barang' => 'required|string|max:255',
                'tanggal_pembuatan' => 'required|date',
                'harga' => 'required|integer',
                'jumlah' => 'required|integer',
            ]);
            $inventori = Inventori::findOrFail($id);
            $inventori->nama_barang = $request->nama_barang;
            $inventori->tanggal_pembuatan = Carbon::parse($request->tanggal_pembuatan);
            $inventori->harga = $request->harga;
            $inventori->jumlah = $request->jumlah;
            $inventori->save();

            return redirect()->route('inventori')->with('success', 'Data inventori berhasil diperbarui');
        } catch (\Illuminate\Validation\ValidationException | \Exception $e) {
            return redirect()->route('inventori')->with('error', 'Mohon maaf data inventori gagal diperbarui');
        }
    }

    //fungsi update status booking    
    public function updatestatusbooking(Request $request, $id)
    {
        try {
            $request->validate([
                'status'    => 'required|string',
                'karyawan1' => 'nullable|string',
                'nama'      => 'nullable|string',
            ]);

            $booking = Booking::findOrFail($id);

            // Set Status & Karyawan
            $booking->status = $request->status;
            if ($request->filled('karyawan1')) $booking->karyawan1 = $request->karyawan1;
            if ($request->filled('karyawan2')) $booking->karyawan2 = $request->karyawan2;
            if ($request->filled('karyawan3')) $booking->karyawan3 = $request->karyawan3;

            // Update Data Pasien (Menggunakan nama kolom DB yang valid: nama_hewan, berat_hewan, jenis_hewan)
            if ($request->filled('nama')) $booking->nama = $request->nama;
            
            $namaHewanVal = $request->nama_hewan ?: ($request->namahewan ?: null);
            if ($namaHewanVal) $booking->nama_hewan = $namaHewanVal;

            $beratVal = $request->berat_hewan ?: ($request->berat ?: ($request->berathewan ?: null));
            if ($beratVal) $booking->berat_hewan = $beratVal;

            $jenisVal = $request->jenis_hewan ?: ($request->jenis ?: ($request->jenishewan ?: null));
            if ($jenisVal) $booking->jenis_hewan = $jenisVal;

            if ($request->filled('tanggal')) {
                $booking->keluar = Carbon::parse($request->tanggal);
            }

            // Financial & POS
            $booking->dp = $request->dp ?? 0;
            $booking->diskon = $request->diskon ?? 0;
            $booking->pajak = $request->pajak ?? 0;
            if ($request->filled('metode_pembayaran')) $booking->metode_pembayaran = $request->metode_pembayaran;

            // Vital signs & EMR SOAP
            if ($request->filled('suhu_tubuh')) $booking->suhu_tubuh = $request->suhu_tubuh;
            if ($request->filled('denyut_jantung')) $booking->denyut_jantung = $request->denyut_jantung;
            if ($request->filled('crt')) $booking->crt = $request->crt;
            if ($request->filled('subjective')) $booking->subjective = $request->subjective;
            if ($request->filled('objective')) $booking->objective = $request->objective;
            if ($request->filled('assessment')) $booking->assessment = $request->assessment;
            if ($request->filled('plan')) $booking->plan = $request->plan;
            if ($request->filled('tanggal_kontrol')) $booking->tanggal_kontrol = Carbon::parse($request->tanggal_kontrol);

            // Layanan & Tindakan
            if ($request->has('vaksin')) $booking->vaksin = $request->vaksin;
            if ($request->has('vaksin_kuantitas')) $booking->vaksin_kuantitas = $request->vaksin_kuantitas;
            if ($request->has('vaksin_harga')) $booking->vaksin_harga = $request->vaksin_harga;

            if ($request->has('grooming')) $booking->grooming = $request->grooming;
            if ($request->has('grooming_kuantitas')) $booking->grooming_kuantitas = $request->grooming_kuantitas;
            if ($request->has('grooming_harga')) $booking->grooming_harga = $request->grooming_harga;

            if ($request->has('operasi')) $booking->operasi = $request->operasi;
            if ($request->has('operasi_kuantitas')) $booking->operasi_kuantitas = $request->operasi_kuantitas;
            if ($request->has('operasi_harga')) $booking->operasi_harga = $request->operasi_harga;

            if ($request->has('lainnya')) $booking->lainnya = $request->lainnya;
            if ($request->has('lainnya_kuantitas')) $booking->lainnya_kuantitas = $request->lainnya_kuantitas;
            if ($request->has('lainnya_harga')) $booking->lainnya_harga = $request->lainnya_harga;

            if ($request->has('jasa_dokter')) $booking->jasa_dokter = $request->jasa_dokter;
            if ($request->has('jasa_dokter_kuantitas')) $booking->jasa_dokter_kuantitas = $request->jasa_dokter_kuantitas;
            if ($request->has('jasa_dokter_harga')) $booking->jasa_dokter_harga = $request->jasa_dokter_harga;

            if ($request->has('diagnosa_penunjang')) $booking->diagnosa_penunjang = $request->diagnosa_penunjang;
            if ($request->has('diagnosa_penunjang_kuantitas')) $booking->diagnosa_penunjang_kuantitas = $request->diagnosa_penunjang_kuantitas;
            if ($request->has('diagnosa_penunjang_harga')) $booking->diagnosa_penunjang_harga = $request->diagnosa_penunjang_harga;

            if ($request->has('transportasi')) $booking->transportasi = $request->transportasi;
            if ($request->has('transportasi_kuantitas')) $booking->transportasi_kuantitas = $request->transportasi_kuantitas;
            if ($request->has('transport_harga')) $booking->transport_harga = $request->transport_harga;
            if ($request->has('total_transport')) $booking->total_trasport = $request->total_transport;

            // Total Calculation
            $subtotalLayanan = ($request->vaksin_harga ?? 0) +
                ($request->grooming_harga ?? 0) +
                ($request->operasi_harga ?? 0) +
                ($request->lainnya_harga ?? 0) +
                ($request->jasa_dokter_harga ?? 0) +
                ($request->diagnosa_penunjang_harga ?? 0) +
                ($request->total_transport ?? 0);
            $totalObat = Transaksi::where('booking_id', $id)->sum('total');
            $totalGross = $subtotalLayanan + $totalObat;
            $booking->total = max(0, $totalGross - ($request->diskon ?? 0) + ($request->pajak ?? 0));

            $booking->save();

            if ($request->status == 'Telah diselesaikan' || $request->status == 'Selesai') {
                return redirect()->route('booking-completed')->with('success', 'Status booking berhasil diselesaikan.');
            }

            return redirect()->route('booking-uncompleted')->with('success', 'Status booking berhasil diperbarui menjadi ' . $request->status);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error: ' . $e->getMessage(), ['errors' => $e->errors()]);
            return redirect()->back()->with('error', 'Data yang diberikan tidak valid.');
        } catch (\Exception $e) {
            Log::error('General error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Mohon maaf status booking gagal diubah: ' . $e->getMessage());
        }
    }


    //fungsi update layanan
    public function updatelayanan(Request $request, $id)
    {
        try {
            $request->validate([
                'nama' => 'required|string|max:255',
                'jenis' => 'required|string|max:255',
                'harga' => 'required|integer',
            ]);
            $layanan = layanan::findOrFail($id);
            $layanan->nama = $request->nama;
            $layanan->jenis = $request->jenis;
            $layanan->harga = $request->harga;
            $layanan->save();

            return redirect()->route('layanan')->with('success', 'Data layanan berhasil diperbarui');
        } catch (\Illuminate\Validation\ValidationException | \Exception $e) {
            return redirect()->route('layanan')->with('error', 'Mohon maaf data layanan gagal diperbarui');
        }
    }

    //fungsi delete akun karyawan
    public function deleteakunkaryawan($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return redirect()->route('karyawan')->with('success', 'Data karyawan berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('karyawan')->with('error', 'Mohon Maaf Data karyawan gagal dihapus');
        }
    }

    //fungsi delete barang inventori
    public function deleteinventori($id)
    {
        try {
            $inventori = Inventori::findOrFail($id);
            $inventori->delete();
            return redirect()->route('inventori')->with('success', 'Barang berhasil dihapus dari inventori');
        } catch (\Exception $e) {
            return redirect()->route('inventori')->with('error', 'Mohon Maaf barang gagal dihapus dari inventori');
        }
    }

    //fungsi delete layanan
    public function deletelayanan($id)
    {
        try {
            $layanan = layanan::findOrFail($id);
            $layanan->delete();
            return redirect()->route('layanan')->with('success', 'Layanan berhasil dihapus');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->route('layanan')->with('error', 'Mohon Maaf layanan gagal dihapus');
        }
    }

    public function deleteItem($id)
    {
        // Ambil data transaksi berdasarkan ID
        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return redirect()->route('manajemen-stok')->withErrors(['message' => 'Transaksi tidak ditemukan']);
        }

        // Ambil data inventori berdasarkan ID dari transaksi
        $inventori = Inventori::find($transaksi->inventori_id);

        if ($inventori) {
            // Tambahkan jumlah inventori sesuai dengan jumlah dari transaksi
            $inventori->jumlah += $transaksi->jumlah;
            $inventori->save();
        }

        // Hapus transaksi
        $transaksi->delete();

        return redirect()->route('manajemen-stok')->with('success', 'Item berhasil dihapus dan inventori diperbarui');
    }

    //fungsi export PDF
    public function exportPDF($id)
    {
        // Ambil data booking / transaksi berdasarkan ID
        $transaksi = Booking::with(['owner', 'ownerPet', 'transaksis.inventori'])->find($id);

        if (!$transaksi) {
            return redirect()->back()->with('error', 'Data booking/transaksi tidak ditemukan');
        }

        $obat = Transaksi::where('booking_id', $id)->with('inventori')->get();
        $totalObat = Transaksi::where('booking_id', $id)->sum('total');

        $totalLayanan =
            ($transaksi->vaksin_harga ?? 0) +
            ($transaksi->grooming_harga ?? 0) +
            ($transaksi->operasi_harga ?? 0) +
            ($transaksi->lainnya_harga ?? 0) +
            ($transaksi->jasa_dokter_harga ?? 0) +
            ($transaksi->diagnosa_penunjang_harga ?? 0) +
            ($transaksi->total_trasport ?? 0);

        $subtotal = $totalObat + $totalLayanan;
        $diskon = $transaksi->diskon ?? 0;
        $pajak = $transaksi->pajak ?? 0;
        $dp = $transaksi->dp ?? 0;

        $grandTotal = max(0, $subtotal - $diskon + $pajak);
        $sisaPembayaran = max(0, $grandTotal - $dp);

        // Backward compatibility variables
        $totalTransaksi = $totalObat;
        $totalHarga = $totalLayanan;
        $pembayaran = $sisaPembayaran;

        // Render PDF view
        $html = view('PDF.nota', compact(
            'transaksi',
            'obat',
            'totalObat',
            'totalLayanan',
            'subtotal',
            'diskon',
            'pajak',
            'dp',
            'grandTotal',
            'sisaPembayaran',
            'totalTransaksi',
            'totalHarga',
            'pembayaran'
        ))->render();

        $pdf = Pdf::loadHTML($html);
        $pdf->setBasePath(public_path());
        $pdf->set_option('isRemoteEnabled', true);

        return $pdf->stream('invoice_' . ($transaksi->kode_booking ?? $transaksi->id) . '.pdf');
    }

    // Quick stage transition handler for PRD Patient Queue
    public function updateStage(Request $request, $id)
    {
        try {
            $booking = Booking::findOrFail($id);
            $targetStage = $request->target_status ?? 'Memulai';
            $booking->status = $targetStage;
            $booking->save();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status antrean berhasil diperbarui menjadi ' . $targetStage,
                    'status'  => $targetStage
                ]);
            }

            return redirect()->back()->with('success', 'Status antrean pasien berhasil diperbarui ke stage: ' . $targetStage);
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal memperbarui stage: ' . $e->getMessage());
        }
    }

    /**
     * Store a daily inpatient monitoring log (checkup progress) for a patient.
     */
    public function storePetDailyLog(Request $request)
    {
        try {
            $request->validate([
                'booking_id'        => 'required|exists:bookings,id',
                'suhu_tubuh'        => 'nullable|string',
                'denyut_jantung'    => 'nullable|string',
                'berat_badan'       => 'nullable|numeric',
                'crt'               => 'nullable|string',
                'nafsu_makan'       => 'nullable|string',
                'catatan_observasi' => 'nullable|string',
                'petugas'           => 'nullable|string',
            ]);

            $booking = Booking::findOrFail($request->booking_id);

            // Create new daily progress log
            PetDailyLog::create([
                'booking_id'        => $booking->id,
                'owner_pet_id'      => $booking->owner_pet_id,
                'tanggal_log'       => $request->tanggal_log ? Carbon::parse($request->tanggal_log) : now(),
                'suhu_tubuh'        => $request->suhu_tubuh,
                'denyut_jantung'    => $request->denyut_jantung,
                'berat_badan'       => $request->berat_badan,
                'crt'               => $request->crt,
                'nafsu_makan'       => $request->nafsu_makan,
                'catatan_observasi' => $request->catatan_observasi,
                'petugas'           => $request->petugas ?: ($booking->karyawan1 ?: auth()->user()->name),
            ]);

            // Update latest vital values on booking record
            if ($request->filled('suhu_tubuh')) $booking->suhu_tubuh = $request->suhu_tubuh;
            if ($request->filled('denyut_jantung')) $booking->denyut_jantung = $request->denyut_jantung;
            if ($request->filled('crt')) $booking->crt = $request->crt;
            if ($request->filled('berat_badan')) {
                $booking->berat_hewan = $request->berat_badan;
            }
            if ($request->filled('catatan_observasi')) {
                $booking->plan = $request->catatan_observasi;
            }
            $booking->save();

            return redirect()->back()->with('success', 'Catatan observasi harian rawat inap berhasil disimpan ke riwayat perkembangan pasien.');
        } catch (\Exception $e) {
            Log::error('Store Pet Daily Log Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan catatan observasi: ' . $e->getMessage());
        }
    }

    /**
     * Delete a daily inpatient monitoring log.
     */
    public function destroyPetDailyLog($id)
    {
        try {
            $log = PetDailyLog::findOrFail($id);
            $log->delete();
            return redirect()->back()->with('success', 'Catatan observasi harian berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus log observasi: ' . $e->getMessage());
        }
    }

    /**
     * Fetch pet progress analytics and historical vital signs for tracking.
     */
    public function getPetAnalytics($id)
    {
        try {
            $booking = Booking::with(['owner', 'ownerPet'])->findOrFail($id);

            $petName = $booking->namahewan ?: ($booking->nama_hewan ?: null);
            $ownerId = $booking->owner_id;
            $ownerPetId = $booking->owner_pet_id;

            // Query all historical bookings for this pet
            $query = Booking::query();

            if ($ownerPetId) {
                $query->where('owner_pet_id', $ownerPetId);
            } else {
                $query->where('owner_id', $ownerId);
                if ($petName) {
                    $query->where('nama_hewan', $petName);
                }
            }

            $historicalBookings = $query->orderBy('tanggal', 'asc')->orderBy('id', 'asc')->get();
            $bookingIds = $historicalBookings->pluck('id')->toArray();

            // Fetch all daily monitoring logs for this pet / bookings
            $dailyLogs = PetDailyLog::whereIn('booking_id', $bookingIds)
                ->orWhere(function($q) use ($ownerPetId) {
                    if ($ownerPetId) $q->where('owner_pet_id', $ownerPetId);
                })
                ->orderBy('tanggal_log', 'asc')
                ->get();

            // Combine both booking events and daily monitoring logs into a unified chronological trajectory
            $combinedEntries = [];

            foreach ($historicalBookings as $b) {
                $dt = $b->created_at ? Carbon::parse($b->created_at) : ($b->tanggal ? Carbon::parse($b->tanggal) : now());
                $combinedEntries[] = [
                    'type'         => 'booking',
                    'timestamp'    => $dt->timestamp,
                    'date_display' => $dt->format('d M Y'),
                    'time_display' => $b->jam ?? $dt->format('H:i'),
                    'weight'       => $b->berat_hewan ?: ($b->berathewan ?: ($b->berat ?: null)),
                    'temp'         => $b->suhu_tubuh,
                    'pulse'        => $b->denyut_jantung,
                    'crt'          => $b->crt ?? '-',
                    'title'        => 'Kunjungan: ' . ($b->kode_booking ?? ('BOOK-' . $b->id)),
                    'status'       => $b->status ?? 'Kedatangan',
                    'dokter'       => $b->karyawan1 ?? 'Dokter Umum',
                    'layanan'      => $b->jasa_dokter ?? ($b->grooming ?? ($b->operasi ?? ($b->vaksin ?? 'Pemeriksaan'))),
                    'keluhan'      => $b->keluhan ?? '-',
                    'subjective'   => $b->soap_s ?? '-',
                    'objective'    => $b->soap_o ?? '-',
                    'assessment'   => $b->soap_a ?? '-',
                    'plan'         => $b->soap_p ?? '-',
                    'id'           => $b->id,
                ];
            }

            foreach ($dailyLogs as $log) {
                $dt = Carbon::parse($log->tanggal_log);
                $combinedEntries[] = [
                    'type'         => 'daily_log',
                    'timestamp'    => $dt->timestamp,
                    'date_display' => $dt->format('d M Y'),
                    'time_display' => $dt->format('H:i'),
                    'weight'       => $log->berat_badan,
                    'temp'         => $log->suhu_tubuh,
                    'pulse'        => $log->denyut_jantung,
                    'crt'          => $log->crt ?? '-',
                    'title'        => 'Observasi Harian Rawat Inap',
                    'status'       => 'Rawat Inap Log',
                    'dokter'       => $log->petugas ?? 'Staf Rawat Inap',
                    'layanan'      => 'Monitoring Harian (Nafsu Makan: ' . ($log->nafsu_makan ?? '-') . ')',
                    'keluhan'      => 'Catatan Observasi: ' . ($log->catatan_observasi ?? '-'),
                    'subjective'   => 'Nafsu Makan: ' . ($log->nafsu_makan ?? '-'),
                    'objective'    => 'Suhu: ' . ($log->suhu_tubuh ?? '-') . ' °C | Pulse: ' . ($log->denyut_jantung ?? '-') . ' bpm | CRT: ' . ($log->crt ?? '-'),
                    'assessment'   => 'Perkembangan Harian Pasien Rawat Inap',
                    'plan'         => $log->catatan_observasi ?? 'Terapi Lanjutan Ward',
                    'id'           => 'log-' . $log->id,
                ];
            }

            // Sort combined entries chronologically
            usort($combinedEntries, function($a, $b) {
                return $a['timestamp'] <=> $b['timestamp'];
            });

            // Prepare chart & timeline datasets
            $labels = [];
            $weightData = [];
            $tempData = [];
            $heartRateData = [];
            $timeline = [];

            foreach ($combinedEntries as $entry) {
                // Extract numeric weight
                $numericWeight = null;
                if ($entry['weight'] !== null) {
                    preg_match('/[0-9]+(\.[0-9]+)?/', str_replace(',', '.', (string)$entry['weight']), $matches);
                    if (!empty($matches[0])) {
                        $numericWeight = (float)$matches[0];
                    }
                }

                // Extract numeric temperature
                $numericTemp = null;
                if ($entry['temp'] !== null) {
                    preg_match('/[0-9]+(\.[0-9]+)?/', str_replace(',', '.', (string)$entry['temp']), $matches);
                    if (!empty($matches[0])) {
                        $numericTemp = (float)$matches[0];
                    }
                }

                // Extract numeric heart rate
                $numericPulse = null;
                if ($entry['pulse'] !== null) {
                    preg_match('/[0-9]+/', (string)$entry['pulse'], $matches);
                    if (!empty($matches[0])) {
                        $numericPulse = (int)$matches[0];
                    }
                }

                $labels[] = $entry['date_display'] . ' (' . $entry['time_display'] . ')';
                $weightData[] = $numericWeight;
                $tempData[] = $numericTemp;
                $heartRateData[] = $numericPulse;

                $timeline[] = [
                    'id'              => $entry['id'],
                    'kode_booking'    => $entry['title'],
                    'tanggal'         => $entry['date_display'],
                    'jam'             => $entry['time_display'],
                    'status'          => $entry['status'],
                    'keluhan'         => $entry['keluhan'],
                    'dokter'          => $entry['dokter'],
                    'layanan'         => $entry['layanan'],
                    'berat'           => $entry['weight'] ? $entry['weight'] . ' kg' : '-',
                    'suhu'            => $entry['temp'] ? $entry['temp'] . ' °C' : '-',
                    'denyut_jantung'  => $entry['pulse'] ? $entry['pulse'] . ' bpm' : '-',
                    'crt'             => $entry['crt'],
                    'subjective'      => $entry['subjective'],
                    'objective'       => $entry['objective'],
                    'assessment'      => $entry['assessment'],
                    'plan'            => $entry['plan'],
                ];
            }

            // Summary Stats
            $validWeights = array_values(array_filter($weightData, fn($w) => $w !== null));
            $latestWeight = end($validWeights) !== false ? end($validWeights) : null;
            $initialWeight = reset($validWeights) !== false ? reset($validWeights) : null;
            $weightDiff = ($latestWeight && $initialWeight && count($validWeights) > 1) ? round($latestWeight - $initialWeight, 2) : null;
            $weightDiffText = null;
            if ($weightDiff !== null) {
                $weightDiffText = ($weightDiff > 0 ? "+{$weightDiff} kg" : "{$weightDiff} kg");
            }

            $validTemps = array_values(array_filter($tempData, fn($t) => $t !== null));
            $latestTemp = end($validTemps) !== false ? end($validTemps) : null;
            $tempStatus = null;
            if ($latestTemp !== null) {
                if ($latestTemp > 39.2) $tempStatus = 'Demam (Fever)';
                elseif ($latestTemp < 37.5) $tempStatus = 'Hipotermia';
                else $tempStatus = 'Normal';
            }

            $validPulses = array_values(array_filter($heartRateData, fn($p) => $p !== null));
            $latestPulse = end($validPulses) !== false ? end($validPulses) : null;

            return response()->json([
                'success' => true,
                'pet' => [
                    'id'            => $booking->owner_pet_id ?: $booking->id,
                    'nama'          => $booking->namahewan ?: ($booking->nama_hewan ?: 'Pasien'),
                    'jenis'         => $booking->jenishewan ?: ($booking->jenis_hewan ?: 'Kucing'),
                    'owner_nama'    => $booking->nama ?? '-',
                    'owner_telpon'  => $booking->telpon ?? '-',
                    'owner_alamat'  => $booking->alamat ?? '-',
                    'total_visits'  => count($historicalBookings) + count($dailyLogs),
                ],
                'summary' => [
                    'latest_weight'  => $latestWeight ? $latestWeight . ' kg' : '-',
                    'initial_weight' => $initialWeight ? $initialWeight . ' kg' : '-',
                    'weight_diff'    => $weightDiffText,
                    'latest_temp'    => $latestTemp ? $latestTemp . ' °C' : '-',
                    'temp_status'    => $tempStatus,
                    'latest_pulse'   => $latestPulse ? $latestPulse . ' bpm' : '-',
                ],
                'chart' => [
                    'labels'     => $labels,
                    'weight'     => $weightData,
                    'temperature'=> $tempData,
                    'heart_rate' => $heartRateData,
                ],
                'timeline' => array_reverse($timeline),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data statistik: ' . $e->getMessage()
            ], 500);
        }
    }
}
