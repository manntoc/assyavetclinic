<?php

namespace App\AI\Tools\Appointment;

use App\AI\Contracts\AIToolInterface;
use App\Models\booking;
use App\Models\User;

/**
 * GetBookingDetailTool : ambil detail lengkap satu booking by kode atau ID.
 * Mencakup: info umum, tanda vital, SOAP EMR, layanan, transaksi obat, billing.
 */
class GetBookingDetailTool implements AIToolInterface
{
    public function getName(): string
    {
        return 'get_booking_detail';
    }

    public function getDescription(): string
    {
        return 'Ambil detail lengkap satu booking pasien: info umum, tanda vital, rekam medis SOAP, layanan, transaksi obat, dan billing. Gunakan kode booking (BOOK...) atau nama owner/hewan.';
    }

    public function getParameters(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'kode_booking' => [
                    'type'        => 'string',
                    'description' => 'Kode booking (format: BOOK + 7 karakter). Prioritas utama jika tersedia.',
                ],
                'owner' => [
                    'type'        => 'string',
                    'description' => 'Nama owner/pemilik hewan untuk pencarian jika kode tidak diketahui.',
                ],
                'pet' => [
                    'type'        => 'string',
                    'description' => 'Nama hewan/pasien untuk pencarian jika kode tidak diketahui.',
                ],
            ],
        ];
    }

    public function authorize(User $user): bool
    {
        return true;
    }

    public function requiresConfirmation(): bool
    {
        return false;
    }

    public function execute(array $parameters, User $user): array
    {
        $kode  = strtoupper(trim($parameters['kode_booking'] ?? ''));
        $owner = trim($parameters['owner'] ?? '');
        $pet   = trim($parameters['pet'] ?? '');

        $query = booking::with('transaksis.inventori');

        if (!empty($kode)) {
            $query->where('kode_booking', $kode);
        } elseif (!empty($pet)) {
            $query->where('nama_hewan', 'LIKE', "%{$pet}%");
            if (!empty($owner)) {
                $query->where('nama', 'LIKE', "%{$owner}%");
            }
        } elseif (!empty($owner)) {
            $query->where('nama', 'LIKE', "%{$owner}%");
        } else {
            return [
                'status'  => 'error',
                'message' => 'Parameter tidak lengkap. Berikan kode booking, nama owner, atau nama hewan.',
            ];
        }

        $b = $query->latest('tanggal')->first();

        if (!$b) {
            return [
                'status'  => 'not_found',
                'message' => 'Booking tidak ditemukan. Periksa kode booking atau nama yang dicari.',
            ];
        }

        // Build layanan list from denormalized columns
        $layananItems = [];
        $layananDefs  = [
            'jasa_dokter'        => ['Jasa Dokter',         $b->jasa_dokter_kuantitas,        $b->jasa_dokter_harga],
            'vaksin'             => ['Vaksinasi',            $b->vaksin_kuantitas,             $b->vaksin_harga],
            'grooming'           => ['Grooming',             $b->grooming_kuantitas,           $b->grooming_harga],
            'operasi'            => ['Operasi/Tindakan',     $b->operasi_kuantitas,            $b->operasi_harga],
            'diagnosa_penunjang' => ['Diagnosa Penunjang',   $b->diagnosa_penunjang_kuantitas, $b->diagnosa_penunjang_harga],
            'transportasi'       => ['Transportasi',         $b->transportasi_kuantitas,       $b->transport_harga],
            'lainnya'            => ['Lainnya',              $b->lainnya_kuantitas,            $b->lainnya_harga],
        ];
        foreach ($layananDefs as $field => [$label, $qty, $harga]) {
            if (!empty($b->$field)) {
                $layananItems[] = [
                    'nama'  => $label . ': ' . $b->$field,
                    'qty'   => $qty ?? 1,
                    'harga' => 'Rp ' . number_format((int) $harga, 0, ',', '.'),
                ];
            }
        }

        // Transaksi obat
        $transaksiItems = [];
        foreach ($b->transaksis as $t) {
            $namaBarang = $t->inventori->nama_barang ?? 'Barang #' . $t->inventori_id;
            $transaksiItems[] = [
                'barang' => $namaBarang,
                'jumlah' => $t->jumlah,
                'total'  => 'Rp ' . number_format((int) $t->total, 0, ',', '.'),
            ];
        }

        return [
            'status'       => 'success',
            'booking'      => [
                'kode_booking'     => $b->kode_booking,
                'tanggal'          => date('d M Y', strtotime($b->tanggal)),
                'jam'              => $b->jam,
                'tipe_kedatangan'  => $b->tipe_kedatangan,
                'status'           => $b->status,
            ],
            'owner'        => [
                'nama'   => $b->nama,
                'telpon' => $b->telpon,
                'alamat' => $b->alamat,
            ],
            'hewan'        => [
                'nama'        => $b->nama_hewan,
                'jenis'       => $b->jenis_hewan,
                'berat'       => $b->berat_hewan ? $b->berat_hewan . ' kg' : '-',
            ],
            'dokter'       => [
                'utama'  => $b->karyawan1 ?: '-',
                'asisten'=> implode(', ', array_filter([$b->karyawan2, $b->karyawan3])) ?: '-',
            ],
            'tanda_vital'  => [
                'suhu_tubuh'     => $b->suhu_tubuh ? $b->suhu_tubuh . ' °C' : '-',
                'denyut_jantung' => $b->denyut_jantung ? $b->denyut_jantung . ' bpm' : '-',
                'crt'            => $b->crt ?: '-',
            ],
            'emr_soap'     => [
                'subjective' => $b->subjective ?: '-',
                'objective'  => $b->objective  ?: '-',
                'assessment' => $b->assessment ?: '-',
                'plan'       => $b->plan       ?: '-',
            ],
            'keluhan'      => $b->keluhan ?: '-',
            'layanan'      => $layananItems,
            'transaksi'    => $transaksiItems,
            'billing'      => [
                'dp'               => 'Rp ' . number_format((int) $b->dp, 0, ',', '.'),
                'diskon'           => 'Rp ' . number_format((int) $b->diskon, 0, ',', '.'),
                'pajak'            => 'Rp ' . number_format((int) $b->pajak, 0, ',', '.'),
                'total'            => 'Rp ' . number_format((int) $b->total, 0, ',', '.'),
                'metode_pembayaran' => $b->metode_pembayaran ?: '-',
            ],
            'tanggal_kontrol' => $b->tanggal_kontrol
                ? date('d M Y', strtotime($b->tanggal_kontrol))
                : '-',
        ];
    }
}
