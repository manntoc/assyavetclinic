<?php

namespace App\AI\Tools\Report;

use App\AI\Contracts\AIToolInterface;
use App\Models\Transaksi;
use App\Models\booking;
use App\Models\User;

class GetTransaksiLogTool implements AIToolInterface
{
    public function getName(): string
    {
        return 'get_transaksi_log';
    }

    public function getDescription(): string
    {
        return 'Lihat catatan pengambilan obat/barang dari inventori (transaksi stok) per booking, tanggal, atau nama obat.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'booking_code' => [
                    'type' => 'string',
                    'description' => 'Kode booking (BOOKXXXXXXX) untuk filter per kunjungan'
                ],
                'query' => [
                    'type' => 'string',
                    'description' => 'Nama obat/barang atau nama owner/hewan'
                ],
                'date' => [
                    'type' => 'string',
                    'description' => 'Tanggal pengambilan (YYYY-MM-DD, opsional, default hari ini)'
                ],
                'period' => [
                    'type' => 'string',
                    'description' => 'Periode: today, week, month (digunakan jika date kosong)'
                ],
            ],
            'required' => []
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
        $bookingCode = trim($parameters['booking_code'] ?? '');
        $query       = trim($parameters['query'] ?? '');
        $date        = trim($parameters['date'] ?? '');
        $period      = trim($parameters['period'] ?? 'today');

        $transaksiQuery = Transaksi::with(['booking.owner', 'booking.ownerPet', 'inventori'])
            ->orderBy('created_at', 'desc');

        // Filter by booking code
        if (!empty($bookingCode)) {
            $transaksiQuery->whereHas('booking', function ($q) use ($bookingCode) {
                $q->where('kode_booking', 'LIKE', "%{$bookingCode}%");
            });
        }

        // Filter by obat name or owner/pet name
        if (!empty($query)) {
            $transaksiQuery->where(function ($q) use ($query) {
                $q->whereHas('inventori', function ($iq) use ($query) {
                    $iq->where('nama_barang', 'LIKE', "%{$query}%");
                })->orWhereHas('booking', function ($bq) use ($query) {
                    $bq->where('nama', 'LIKE', "%{$query}%")
                       ->orWhere('nama_hewan', 'LIKE', "%{$query}%");
                });
            });
        }

        // Filter by date or period
        if (!empty($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $transaksiQuery->whereDate('created_at', $date);
            $periodLabel = $date;
        } else {
            switch ($period) {
                case 'week':
                    $transaksiQuery->where('created_at', '>=', now()->subDays(7));
                    $periodLabel = '7 hari terakhir';
                    break;
                case 'month':
                    $transaksiQuery->whereMonth('created_at', date('m'))
                                   ->whereYear('created_at', date('Y'));
                    $periodLabel = 'Bulan ' . date('F Y');
                    break;
                default: // today
                    $transaksiQuery->whereDate('created_at', date('Y-m-d'));
                    $periodLabel = 'Hari ini (' . date('d M Y') . ')';
            }
        }

        $transaksis = $transaksiQuery->take(20)->get();

        if ($transaksis->isEmpty()) {
            return [
                'found'   => false,
                'message' => "Tidak ada catatan pengambilan obat untuk periode/filter yang diminta.",
                'period'  => $periodLabel ?? $period,
            ];
        }

        $totalNilai = 0;
        $items = $transaksis->map(function ($t) use (&$totalNilai) {
            $totalNilai += $t->total;
            return [
                'kode_booking'  => $t->booking->kode_booking ?? '-',
                'owner'         => $t->booking->owner->nama_owner ?? ($t->booking->nama ?? '-'),
                'hewan'         => $t->booking->ownerPet->nama_hewan ?? ($t->booking->nama_hewan ?? '-'),
                'nama_barang'   => $t->inventori->nama_barang ?? '-',
                'jumlah'        => $t->jumlah,
                'total'         => $t->total,
                'total_formatted' => 'Rp ' . number_format($t->total, 0, ',', '.'),
                'tanggal'       => $t->created_at ? $t->created_at->format('d M Y H:i') : '-',
            ];
        })->values()->toArray();

        return [
            'found'               => true,
            'period'              => $periodLabel ?? $period,
            'total_records'       => count($items),
            'total_nilai'         => $totalNilai,
            'total_nilai_formatted' => 'Rp ' . number_format($totalNilai, 0, ',', '.'),
            'items'               => $items,
        ];
    }
}
