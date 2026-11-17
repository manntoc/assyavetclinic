<?php

namespace App\Http\Controllers;

use App\Models\booking;
use App\Models\Transaksi;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanOmzetController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->parseFilters($request);

        // Fetch query builder for matching bookings
        $query = $this->buildBookingQuery($filters);

        // Financial KPIs
        $bookingsForKpi = (clone $query)->get();
        $totalOmzetClean = $bookingsForKpi->sum('total');
        $totalDp         = $bookingsForKpi->sum('dp');
        $totalDiskon     = $bookingsForKpi->sum('diskon');
        $totalPajak      = $bookingsForKpi->sum('pajak');
        $totalTransaksi  = $bookingsForKpi->count();
        $aov             = $totalTransaksi > 0 ? round($totalOmzetClean / $totalTransaksi) : 0;

        // Breakdown Gross Revenue per Service & Inventory Category
        $catJasaDokter = $bookingsForKpi->sum('jasa_dokter_harga');
        $catVaksin     = $bookingsForKpi->sum('vaksin_harga');
        $catGrooming   = $bookingsForKpi->sum('grooming_harga');
        $catOperasi    = $bookingsForKpi->sum('operasi_harga');
        $catDiagnosa   = $bookingsForKpi->sum('diagnosa_penunjang_harga');
        $catTransport  = $bookingsForKpi->sum(function ($b) {
            return $b->total_trasport ?: ($b->transport_harga ?: 0);
        });
        $catLainnya    = $bookingsForKpi->sum('lainnya_harga');

        // Sum inventory / medicine sales for matching bookings
        $bookingIds = $bookingsForKpi->pluck('id');
        $catObat    = Transaksi::whereIn('booking_id', $bookingIds)->sum('total');

        $totalGross = $catJasaDokter + $catVaksin + $catGrooming + $catOperasi + $catDiagnosa + $catTransport + $catLainnya + $catObat;

        // Category breakdown array for Chart
        $categoryBreakdown = [
            'Jasa Dokter'       => (int) $catJasaDokter,
            'Vaksin'            => (int) $catVaksin,
            'Grooming'          => (int) $catGrooming,
            'Operasi'           => (int) $catOperasi,
            'Diagnosa Penunjang'=> (int) $catDiagnosa,
            'Obat & Inventori'  => (int) $catObat,
            'Transportasi'      => (int) $catTransport,
            'Lainnya'           => (int) $catLainnya,
        ];

        // Payment Method breakdown for Chart
        $paymentMethodsRaw = $bookingsForKpi->groupBy(function ($item) {
            return !empty($item->metode_pembayaran) ? title_case_payment($item->metode_pembayaran) : 'Belum Ditentukan';
        });

        $paymentBreakdown = [];
        foreach ($paymentMethodsRaw as $method => $items) {
            $paymentBreakdown[$method] = [
                'count' => $items->count(),
                'total' => (int) $items->sum('total'),
            ];
        }

        // Time Series Data for Line Chart (Daily / Monthly)
        $chartTrend = $this->generateTrendChartData($filters['start_date'], $filters['end_date'], $bookingsForKpi);

        // Paginated transactions table
        $transactionsList = (clone $query)
            ->with(['transaksis.inventori', 'owner', 'ownerPet'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('laporan_omzet.index', compact(
            'filters',
            'totalOmzetClean',
            'totalGross',
            'totalDp',
            'totalDiskon',
            'totalPajak',
            'totalTransaksi',
            'aov',
            'categoryBreakdown',
            'paymentBreakdown',
            'chartTrend',
            'transactionsList'
        ));
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->parseFilters($request);
        $query   = $this->buildBookingQuery($filters);

        $bookings = $query->with(['transaksis.inventori', 'owner', 'ownerPet'])
            ->orderBy('tanggal', 'asc')
            ->get();

        $totalOmzetClean = $bookings->sum('total');
        $totalDp         = $bookings->sum('dp');
        $totalDiskon     = $bookings->sum('diskon');
        $totalPajak      = $bookings->sum('pajak');
        $totalTransaksi  = $bookings->count();
        $aov             = $totalTransaksi > 0 ? round($totalOmzetClean / $totalTransaksi) : 0;

        $catJasaDokter = $bookings->sum('jasa_dokter_harga');
        $catVaksin     = $bookings->sum('vaksin_harga');
        $catGrooming   = $bookings->sum('grooming_harga');
        $catOperasi    = $bookings->sum('operasi_harga');
        $catDiagnosa   = $bookings->sum('diagnosa_penunjang_harga');
        $catTransport  = $bookings->sum(function ($b) {
            return $b->total_trasport ?: ($b->transport_harga ?: 0);
        });
        $catLainnya    = $bookings->sum('lainnya_harga');
        $bookingIds    = $bookings->pluck('id');
        $catObat       = Transaksi::whereIn('booking_id', $bookingIds)->sum('total');

        $totalGross = $catJasaDokter + $catVaksin + $catGrooming + $catOperasi + $catDiagnosa + $catTransport + $catLainnya + $catObat;

        $categoryBreakdown = [
            'Jasa Dokter'       => (int) $catJasaDokter,
            'Vaksin'            => (int) $catVaksin,
            'Grooming'          => (int) $catGrooming,
            'Operasi'           => (int) $catOperasi,
            'Diagnosa Penunjang'=> (int) $catDiagnosa,
            'Obat & Inventori'  => (int) $catObat,
            'Transportasi'      => (int) $catTransport,
            'Lainnya'           => (int) $catLainnya,
        ];

        $pdf = Pdf::loadView('PDF.laporan_omzet', compact(
            'filters',
            'bookings',
            'totalOmzetClean',
            'totalGross',
            'totalDp',
            'totalDiskon',
            'totalPajak',
            'totalTransaksi',
            'aov',
            'categoryBreakdown'
        ))->setPaper('a4', 'landscape');

        $fileName = 'Laporan_Omzet_Assyavet_' . $filters['start_date']->format('Ymd') . '_sd_' . $filters['end_date']->format('Ymd') . '.pdf';

        return $pdf->stream($fileName);
    }

    private function parseFilters(Request $request): array
    {
        $range = $request->get('range', 'this_month');
        $status = $request->get('status', 'completed');
        $metode = $request->get('metode_pembayaran', 'all');
        $search = $request->get('search', '');

        $now = Carbon::now();

        switch ($range) {
            case 'today':
                $startDate = Carbon::today()->startOfDay();
                $endDate   = Carbon::today()->endOfDay();
                break;
            case '7days':
                $startDate = Carbon::today()->subDays(6)->startOfDay();
                $endDate   = Carbon::today()->endOfDay();
                break;
            case 'this_month':
                $startDate = $now->copy()->startOfMonth()->startOfDay();
                $endDate   = $now->copy()->endOfMonth()->endOfDay();
                break;
            case 'last_month':
                $startDate = $now->copy()->subMonth()->startOfMonth()->startOfDay();
                $endDate   = $now->copy()->subMonth()->endOfMonth()->endOfDay();
                break;
            case 'this_year':
                $startDate = $now->copy()->startOfYear()->startOfDay();
                $endDate   = $now->copy()->endOfYear()->endOfDay();
                break;
            case 'custom':
                $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : $now->copy()->startOfMonth()->startOfDay();
                $endDate   = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : $now->copy()->endOfDay();
                break;
            default:
                $range     = 'this_month';
                $startDate = $now->copy()->startOfMonth()->startOfDay();
                $endDate   = $now->copy()->endOfMonth()->endOfDay();
                break;
        }

        return [
            'range'             => $range,
            'start_date'        => $startDate,
            'end_date'          => $endDate,
            'status'            => $status,
            'metode_pembayaran' => $metode,
            'search'            => $search,
        ];
    }

    private function buildBookingQuery(array $filters)
    {
        $query = booking::query();

        // Date range filter based on booking date
        $query->whereBetween('tanggal', [$filters['start_date']->format('Y-m-d'), $filters['end_date']->format('Y-m-d')]);

        // Status filter
        if ($filters['status'] === 'completed') {
            $query->whereIn('status', ['Telah diselesaikan', 'Selesai']);
        } elseif ($filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        // Payment Method filter
        if ($filters['metode_pembayaran'] !== 'all') {
            if ($filters['metode_pembayaran'] === 'Belum Ditentukan') {
                $query->where(function ($q) {
                    $q->whereNull('metode_pembayaran')->orWhere('metode_pembayaran', '');
                });
            } else {
                $query->where('metode_pembayaran', 'like', '%' . $filters['metode_pembayaran'] . '%');
            }
        }

        // Search text
        if (!empty($filters['search'])) {
            $keyword = $filters['search'];
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', "%{$keyword}%")
                    ->orWhere('nama_hewan', 'like', "%{$keyword}%")
                    ->orWhere('namahewan', 'like', "%{$keyword}%")
                    ->orWhere('kode_booking', 'like', "%{$keyword}%")
                    ->orWhere('telpon', 'like', "%{$keyword}%");
            });
        }

        return $query;
    }

    private function generateTrendChartData(Carbon $startDate, Carbon $endDate, $bookingsCollection): array
    {
        $diffInDays = $startDate->diffInDays($endDate);

        $labels = [];
        $values = [];
        $counts = [];

        if ($diffInDays <= 31) {
            // Group Daily
            $period = CarbonPeriod::create($startDate, $endDate);
            $grouped = $bookingsCollection->groupBy(function ($item) {
                return Carbon::parse($item->tanggal)->format('Y-m-d');
            });

            foreach ($period as $date) {
                $dateKey = $date->format('Y-m-d');
                $labels[] = $date->format('d M');
                
                $items = $grouped->get($dateKey, collect());
                $values[] = (int) $items->sum('total');
                $counts[] = $items->count();
            }
        } else {
            // Group Monthly
            $grouped = $bookingsCollection->groupBy(function ($item) {
                return Carbon::parse($item->tanggal)->format('Y-m');
            });

            $current = $startDate->copy()->startOfMonth();
            $last    = $endDate->copy()->endOfMonth();

            while ($current <= $last) {
                $monthKey = $current->format('Y-m');
                $labels[] = $current->format('M Y');

                $items = $grouped->get($monthKey, collect());
                $values[] = (int) $items->sum('total');
                $counts[] = $items->count();

                $current->addMonth();
            }
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'counts' => $counts,
        ];
    }
}

function title_case_payment($string) {
    if (empty($string)) return 'Belum Ditentukan';
    return ucfirst(strtolower(trim($string)));
}
