<?php

namespace App\Http\Controllers;

use App\Models\booking;
use App\Models\Inventori;
use App\Models\layanan;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\Owner;
use App\Models\OwnerPet;
use Illuminate\Http\Request;


class AllController extends Controller
{
    public function index()
    {
        $karyawan = User::where('role', 'karyawan')->get();
        $inventori = Inventori::all();
        $layanan = layanan::all();
        $layananTerbaru = layanan::latest()->take(5)->get();
        $booking = booking::all();
        $bookingbelum = booking::where('status', 'belum dikerjakan')->get();
        $bookingbelums = booking::where('status', 'belum dikerjakan')->latest()->take(5)->get();
        $bookinngselesai = booking::where('status', 'Telah diselesaikan')->get();
        $lastThreeUsers = $karyawan->slice(-3);
        $events = $booking->map(function ($b) {
            $jam = $b->jam ?? '09:00:00';
            $namaHewan = $b->namahewan ?: ($b->nama_hewan ?: 'Booking Hewan');
            $ownerNama = $b->nama ?? '-';
            $jenisHewan = $b->jenishewan ?: ($b->jenis_hewan ?: '-');
            $status = $b->status ?? 'belum dikerjakan';
            $isSelesai = strripos($status, 'selesai') !== false;

            return [
                'id' => $b->id,
                'title' => $namaHewan . ' (' . $ownerNama . ')',
                'start' => $b->tanggal . ' ' . $jam,
                'backgroundColor' => $isSelesai ? '#a29f9f' : '#2d8aba',
                'borderColor' => $isSelesai ? '#888585' : '#246e96',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'kode_booking' => $b->kode_booking ?? ('BOOK-' . $b->id),
                    'nama_hewan' => $namaHewan,
                    'owner' => $ownerNama,
                    'status' => $status,
                    'keluhan' => $b->keluhan ?? 'Tidak ada keluhan',
                    'jenis_hewan' => $jenisHewan,
                    'berat' => $b->berathewan ?: ($b->berat_hewan ?: ($b->berat ?: '-')),
                    'telpon' => $b->telpon ?? '-',
                    'alamat' => $b->alamat ?? '-',
                    'tanggal' => $b->tanggal ? \Carbon\Carbon::parse($b->tanggal)->format('d M Y') : '-'
                ]
            ];
        });
        return view('dashboard', compact(
            'karyawan',
            'bookingbelum',
            'bookinngselesai',
            'lastThreeUsers',
            'events',
            'booking',
            'layanan',
            'bookingbelums',
            'layananTerbaru',
            'inventori'
        ));
    }

    public function karyawan()
    {
        $users = User::where('role', 'karyawan')->get();
        return view('karyawan', compact('users'));
    }

    public function booking()
    {
        $karyawan = User::whereIn('role', ['karyawan', 'admin', 'superadmin'])->get();
        $allBookings = booking::latest()->get();
        $booking = $allBookings;
        
        $kedatangan = booking::whereIn('status', ['belum dikerjakan', 'Kedatangan'])->get();
        $memulai    = booking::where('status', 'Memulai')->get();
        $rawatInap  = booking::where('status', 'Rawat Inap')->get();
        $apotek     = booking::where('status', 'Apotek')->get();
        $pembayaran = booking::where('status', 'Pembayaran')->get();
        $selesai    = booking::whereIn('status', ['Telah diselesaikan', 'Selesai'])->get();

        $layanan = layanan::all();
        $vaksin = layanan::where('jenis', 'Vaksin')->get();
        $grooming = layanan::where('jenis', 'Grooming')->get();
        $operasi = layanan::where('jenis', 'Operasi')->get();
        $lainnya = layanan::where('jenis', 'Lainnya')->get();
        $jasa_dokter = layanan::where('jenis', 'Jasa Dokter')->get();
        $diagnosa = layanan::where('jenis', 'Diagnosa Penunjang')->get();

        $inventori = Inventori::all();
        $patients = OwnerPet::with('owner')->latest()->get();

        return view('pemasukan', compact(
            'booking', 'allBookings', 'kedatangan', 'memulai', 'rawatInap', 'apotek', 'pembayaran', 'selesai',
            'layanan', 'vaksin', 'grooming', 'operasi', 'lainnya', 'jasa_dokter', 'diagnosa', 'karyawan', 'inventori', 'patients'
        ));
    }

   public function complete()
    {
        $booking = booking::where('status', 'Telah diselesaikan')->get();
        $layanan = layanan::all();
        $vaksin = layanan::where('jenis', 'Vaksin')->get();
        $grooming = layanan::where('jenis', 'Grooming')->get();
        $operasi = layanan::where('jenis', 'Operasi')->get();
        $lainnya = layanan::where('jenis', 'Lainnya')->get();
        $jasa_dokter = layanan::where('jenis', 'Jasa Dokter')->get();
        $diagnosa = layanan::where('jenis', 'Diagnosa Penunjang')->get();
        $transaksi = Transaksi::with('inventori', 'booking')->get();
        return view('complete', compact('booking', 'layanan', 'vaksin', 'grooming', 'operasi', 'lainnya', 'jasa_dokter', 'diagnosa', 'transaksi'));
    }

    public function inventori()
    {
        $inventori = Inventori::all();
        return view('barang', compact('inventori'));
    }

    public function manajemenstok()
    {
        $booking = booking::latest()->get();
        $inventori = Inventori::all();
        $transaksi = Transaksi::with('inventori', 'booking')->get();
        return view('ambilbarang', compact('booking', 'inventori', 'transaksi'));
    }

    public function layanan()
    {
        $layanan = layanan::latest()->get();
        return view('layanan', compact('layanan'));
    }

    public function pekerjaansaya()
    {
        $booking = booking::where('status', 'Telah diselesaikan')
            ->where(function ($query) {
                $query->where('karyawan1', auth()->user()->name)
                    ->orWhere('karyawan2', auth()->user()->name)
                    ->orWhere('karyawan3', auth()->user()->name);
            })
            ->get();
        $layanan = layanan::all();
        $vaksin = layanan::where('jenis', 'Vaksin')->get();
        $grooming = layanan::where('jenis', 'Grooming')->get();
        $operasi = layanan::where('jenis', 'Operasi')->get();
        $lainnya = layanan::where('jenis', 'Lainnya')->get();
        $jasa_dokter = layanan::where('jenis', 'Jasa Dokter')->get();
        $diagnosa = layanan::where('jenis', 'Diagnosa Penunjang')->get();
        return view('pekerjaan', compact('booking', 'layanan', 'vaksin', 'grooming', 'operasi', 'lainnya', 'jasa_dokter', 'diagnosa'));
    }

    public function events()
    {
        $bookings = booking::all();
        $events = [];
        foreach ($bookings as $b) {
            $jam = $b->jam ?? '09:00:00';
            $namaHewan = $b->namahewan ?: ($b->nama_hewan ?: 'Booking Hewan');
            $ownerNama = $b->nama ?? '-';
            $jenisHewan = $b->jenishewan ?: ($b->jenis_hewan ?: '-');
            $status = $b->status ?? 'belum dikerjakan';
            $isSelesai = strripos($status, 'selesai') !== false;

            $events[] = [
                'id' => $b->id,
                'title' => $namaHewan . ' (' . $ownerNama . ')',
                'start' => $b->tanggal . ' ' . $jam,
                'backgroundColor' => $isSelesai ? '#10b981' : '#f59e0b',
                'borderColor' => $isSelesai ? '#059669' : '#d97706',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'kode_booking' => $b->kode_booking ?? ('BOOK-' . $b->id),
                    'nama_hewan' => $namaHewan,
                    'owner' => $ownerNama,
                    'status' => $status,
                    'keluhan' => $b->keluhan ?? 'Tidak ada keluhan',
                    'jenis_hewan' => $jenisHewan,
                    'berat' => $b->berathewan ?: ($b->berat_hewan ?: ($b->berat ?: '-')),
                    'telpon' => $b->telpon ?? '-',
                    'alamat' => $b->alamat ?? '-',
                    'tanggal' => $b->tanggal ? \Carbon\Carbon::parse($b->tanggal)->format('d M Y') : '-'
                ]
            ];
        }

        return response()->json($events);
    }

    public function searchBooking(Request $request)
    {
        $keyword = $request->keyword;

        $pets = OwnerPet::with('owner')
            ->when($keyword, function ($query) use ($keyword) {

                $query->where('nama', 'like', "%{$keyword}%")
                    ->orWhereHas('owner', function ($q) use ($keyword) {
                        $q->where('nama', 'like', "%{$keyword}%");
                    });

            })
            ->latest()
            ->limit(20)
            ->get();

        return response()->json($pets);
    }
}
