<?php

namespace App\AI\Tools\Appointment;

use App\AI\Contracts\AIToolInterface;
use App\Models\booking;
use App\Models\User;

class GetAppointmentsTool implements AIToolInterface
{
    public function getName(): string
    {
        return 'get_appointments';
    }

    public function getDescription(): string
    {
        return 'Ambil daftar booking/antrean pasien klinik berdasarkan periode (hari ini, kemarin, besok, bulan lalu, bulan ini, mendatang).';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'period' => [
                    'type' => 'string',
                    'description' => 'Periode booking: today, yesterday, tomorrow, last_month, this_month, upcoming, all'
                ],
                'query' => [
                    'type' => 'string',
                    'description' => 'Nama owner, pasien, atau dokter (opsional)'
                ]
            ]
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
        $period = $parameters['period'] ?? 'today';
        $queryStr = trim($parameters['query'] ?? '');

        $query = booking::query();

        if (!empty($queryStr)) {
            $query->where(function($q) use ($queryStr) {
                $q->where('nama', 'LIKE', "%{$queryStr}%")
                  ->orWhere('nama_hewan', 'LIKE', "%{$queryStr}%")
                  ->orWhere('kode_booking', 'LIKE', "%{$queryStr}%")
                  ->orWhere('karyawan1', 'LIKE', "%{$queryStr}%");
            });
        }

        $periodTitle = 'Hari Ini (' . date('d M Y') . ')';

        switch ($period) {
            case 'yesterday':
                $query->whereDate('tanggal', date('Y-m-d', strtotime('-1 day')));
                $periodTitle = 'Kemarin (' . date('d M Y', strtotime('-1 day')) . ')';
                break;
            case 'tomorrow':
                $query->whereDate('tanggal', date('Y-m-d', strtotime('+1 day')));
                $periodTitle = 'Besok (' . date('d M Y', strtotime('+1 day')) . ')';
                break;
            case 'last_month':
                $lastMonth = date('m', strtotime('-1 month'));
                $year = date('Y', strtotime('-1 month'));
                $query->whereMonth('tanggal', $lastMonth)->whereYear('tanggal', $year);
                $periodTitle = 'Bulan Lalu (' . date('F Y', strtotime('-1 month')) . ')';
                break;
            case 'this_month':
                $query->whereMonth('tanggal', date('m'))->whereYear('tanggal', date('Y'));
                $periodTitle = 'Bulan Ini (' . date('F Y') . ')';
                break;
            case 'upcoming':
                $query->whereDate('tanggal', '>=', date('Y-m-d'));
                $periodTitle = 'Mendatang';
                break;
            case 'all':
                $periodTitle = 'Terbaru';
                break;
            case 'today':
            default:
                $query->whereDate('tanggal', date('Y-m-d'));
                $periodTitle = 'Hari Ini (' . date('d M Y') . ')';
                break;
        }

        $bookings = $query->latest('tanggal')->take(10)->get();

        if ($bookings->isEmpty()) {
            return [
                'found' => false,
                'period' => $period,
                'title' => $periodTitle,
                'message' => "Belum ada data antrean/booking untuk periode **{$periodTitle}**."
            ];
        }

        $results = [];
        foreach ($bookings as $b) {
            $results[] = [
                'id' => $b->id,
                'kode_booking' => $b->kode_booking,
                'owner' => $b->nama,
                'pet' => $b->nama_hewan,
                'jenis_hewan' => $b->jenis_hewan,
                'dokter' => $b->karyawan1 ?: 'Belum Ditentukan',
                'status' => $b->status,
                'keluhan' => $b->keluhan,
                'tanggal' => date('d M Y', strtotime($b->tanggal)),
                'url' => route('booking-uncompleted')
            ];
        }

        return [
            'found' => true,
            'period' => $period,
            'title' => $periodTitle,
            'total' => count($results),
            'appointments' => $results
        ];
    }
}
