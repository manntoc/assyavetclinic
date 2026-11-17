<?php

namespace App\AI\Tools\Report;

use App\AI\Contracts\AIToolInterface;
use App\Models\booking;
use App\Models\User;
use Carbon\Carbon;

class CompareOmzetTool implements AIToolInterface
{
    public function getName(): string
    {
        return 'compare_omzet';
    }

    public function getDescription(): string
    {
        return 'Bandingkan omzet klinik antar dua bulan atau dua periode berbeda, tampilkan selisih dan persentase perubahan.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'period_a' => [
                    'type' => 'string',
                    'description' => 'Periode pertama: YYYY-MM (misal 2026-07) atau "this_month", "last_month"'
                ],
                'period_b' => [
                    'type' => 'string',
                    'description' => 'Periode kedua: YYYY-MM atau "this_month", "last_month" (default: bulan ini)'
                ],
            ],
            'required' => ['period_a']
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
        $periodA = trim($parameters['period_a'] ?? 'last_month');
        $periodB = trim($parameters['period_b'] ?? 'this_month');

        [$monthA, $yearA, $labelA] = $this->resolvePeriod($periodA);
        [$monthB, $yearB, $labelB] = $this->resolvePeriod($periodB);

        $omzetA = $this->getOmzet($monthA, $yearA);
        $omzetB = $this->getOmzet($monthB, $yearB);

        $selisih  = $omzetB['total'] - $omzetA['total'];
        $pct      = $omzetA['total'] > 0
                  ? round(($selisih / $omzetA['total']) * 100, 1)
                  : ($omzetB['total'] > 0 ? 100 : 0);

        $trend = $selisih > 0 ? 'naik' : ($selisih < 0 ? 'turun' : 'sama');

        return [
            'found'    => true,
            'period_a' => [
                'label'             => $labelA,
                'total'             => $omzetA['total'],
                'total_formatted'   => 'Rp ' . number_format($omzetA['total'], 0, ',', '.'),
                'count'             => $omzetA['count'],
            ],
            'period_b' => [
                'label'             => $labelB,
                'total'             => $omzetB['total'],
                'total_formatted'   => 'Rp ' . number_format($omzetB['total'], 0, ',', '.'),
                'count'             => $omzetB['count'],
            ],
            'selisih'             => $selisih,
            'selisih_formatted'   => 'Rp ' . number_format(abs($selisih), 0, ',', '.'),
            'persentase'          => $pct,
            'trend'               => $trend,
            'summary'             => "Omzet {$labelB} {$trend} {$pct}% dibanding {$labelA}.",
        ];
    }

    /** @return array{int, int, string} [month, year, label] */
    private function resolvePeriod(string $period): array
    {
        if ($period === 'this_month' || $period === 'bulan_ini') {
            return [(int) date('m'), (int) date('Y'), 'Bulan ini (' . date('F Y') . ')'];
        }
        if ($period === 'last_month' || $period === 'bulan_lalu') {
            $d = Carbon::now()->subMonth();
            return [$d->month, $d->year, 'Bulan lalu (' . $d->format('F Y') . ')'];
        }
        // YYYY-MM format
        if (preg_match('/^(\d{4})-(\d{2})$/', $period, $m)) {
            $d = Carbon::createFromDate((int)$m[1], (int)$m[2], 1);
            return [(int)$m[2], (int)$m[1], $d->format('F Y')];
        }
        // fallback: treat as this_month
        return [(int) date('m'), (int) date('Y'), 'Bulan ini (' . date('F Y') . ')'];
    }

    private function getOmzet(int $month, int $year): array
    {
        $q = booking::whereIn('status', ['Telah diselesaikan', 'Selesai'])
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year);

        return [
            'total' => (int) $q->sum('total'),
            'count' => $q->count(),
        ];
    }
}
