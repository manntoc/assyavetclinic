<?php

namespace App\AI\Tools\Report;

use App\AI\Contracts\AIToolInterface;
use App\Models\booking;
use App\Models\User;

class GetOmzetReportTool implements AIToolInterface
{
    public function getName(): string
    {
        return 'get_omzet_report';
    }

    public function getDescription(): string
    {
        return 'Ambil laporan omzet bersih, gross revenue, dan volume transaksi klinik berdasarkan periode.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'period' => [
                    'type' => 'string',
                    'description' => 'Periode: today, month, last_month, atau all'
                ]
            ],
            'required' => ['period']
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
        $period = $parameters['period'] ?? 'month';
        $query  = booking::whereIn('status', ['Telah diselesaikan', 'Selesai']);

        if ($period === 'today') {
            $query->whereDate('tanggal', date('Y-m-d'));
            $title = 'Hari Ini (' . date('d M Y') . ')';
        } elseif ($period === 'last_month') {
            $lastMonth = date('m', strtotime('-1 month'));
            $year      = date('Y', strtotime('-1 month'));
            $query->whereMonth('tanggal', $lastMonth)->whereYear('tanggal', $year);
            $title = 'Bulan Lalu (' . date('F Y', strtotime('-1 month')) . ')';
        } elseif ($period === 'year' || $period === 'this_year') {
            $query->whereYear('tanggal', date('Y'));
            $title = 'Tahun ' . date('Y');
        } elseif (preg_match('/^(\d{4})$/', $period, $m)) {
            // Specific year: "2026"
            $query->whereYear('tanggal', $m[1]);
            $title = 'Tahun ' . $m[1];
        } elseif (preg_match('/^(\d{4})-(\d{2})$/', $period, $m)) {
            // Specific month: "2026-07", "2026-03", etc.
            $query->whereYear('tanggal', $m[1])->whereMonth('tanggal', $m[2]);
            $label = \Carbon\Carbon::createFromDate($m[1], $m[2], 1)->translatedFormat('F Y');
            $title = $label;
        } else {
            $query->whereMonth('tanggal', date('m'))->whereYear('tanggal', date('Y'));
            $title = 'Bulan Ini (' . date('F Y') . ')';
        }

        $totalClean = $query->sum('total');
        $totalCount = $query->count();
        $formattedTotal = 'Rp ' . number_format($totalClean, 0, ',', '.');

        return [
            'period' => $period,
            'title' => $title,
            'total_omzet' => $totalClean,
            'total_omzet_formatted' => $formattedTotal,
            'completed_transactions' => $totalCount,
            'url' => route('laporan-omzet.index')
        ];
    }
}
