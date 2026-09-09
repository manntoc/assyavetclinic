<?php

namespace App\AI\Tools\Appointment;

use App\AI\Contracts\AIToolInterface;
use App\Models\booking;
use App\Models\User;

/**
 * GetEmrTool : ambil rekam medis SOAP dan tanda vital dari booking pasien.
 * Cocok untuk query: "diagnosa terakhir Mochi", "rekam medis kucing Rex", "SOAP booking BOOK123".
 */
class GetEmrTool implements AIToolInterface
{
    public function getName(): string
    {
        return 'get_emr';
    }

    public function getDescription(): string
    {
        return 'Ambil rekam medis SOAP (Subjective, Objective, Assessment, Plan) dan tanda vital dari booking pasien. Gunakan untuk pertanyaan tentang diagnosa, keluhan, hasil pemeriksaan, atau rencana pengobatan.';
    }

    public function getParameters(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'kode_booking' => [
                    'type'        => 'string',
                    'description' => 'Kode booking spesifik (BOOK...). Jika diisi, pencarian langsung ke booking ini.',
                ],
                'pet' => [
                    'type'        => 'string',
                    'description' => 'Nama hewan/pasien untuk mencari EMR kunjungan terakhirnya.',
                ],
                'owner' => [
                    'type'        => 'string',
                    'description' => 'Nama pemilik hewan, sebagai filter tambahan.',
                ],
                'limit' => [
                    'type'        => 'integer',
                    'description' => 'Jumlah riwayat EMR yang ditampilkan (default 1 = terbaru saja, maks 5).',
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
        $pet   = trim($parameters['pet'] ?? '');
        $owner = trim($parameters['owner'] ?? '');
        $limit = min((int) ($parameters['limit'] ?? 1), 5);
        if ($limit < 1) $limit = 1;

        $query = booking::query();

        // Filter: setidaknya ada satu field SOAP yang diisi
        $query->where(function ($q) {
            $q->whereNotNull('subjective')
              ->orWhereNotNull('objective')
              ->orWhereNotNull('assessment')
              ->orWhereNotNull('plan');
        });

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
                'message' => 'Parameter tidak lengkap. Berikan kode booking, nama hewan, atau nama owner.',
            ];
        }

        $bookings = $query->latest('tanggal')->take($limit)->get();

        if ($bookings->isEmpty()) {
            return [
                'status'  => 'not_found',
                'message' => 'Rekam medis tidak ditemukan atau belum ada data SOAP untuk pasien yang dicari.',
            ];
        }

        $records = [];
        foreach ($bookings as $b) {
            $records[] = [
                'kode_booking'   => $b->kode_booking,
                'tanggal'        => date('d M Y', strtotime($b->tanggal)),
                'owner'          => $b->nama,
                'hewan'          => $b->nama_hewan,
                'jenis_hewan'    => $b->jenis_hewan,
                'dokter'         => $b->karyawan1 ?: '-',
                'tanda_vital'    => [
                    'suhu_tubuh'     => $b->suhu_tubuh     ? $b->suhu_tubuh . ' °C'  : '-',
                    'denyut_jantung' => $b->denyut_jantung ? $b->denyut_jantung . ' bpm' : '-',
                    'crt'            => $b->crt            ?: '-',
                    'berat'          => $b->berat_hewan    ? $b->berat_hewan . ' kg' : '-',
                ],
                'soap'           => [
                    'subjective' => $b->subjective ?: '-',
                    'objective'  => $b->objective  ?: '-',
                    'assessment' => $b->assessment ?: '-',
                    'plan'       => $b->plan       ?: '-',
                ],
                'keluhan'        => $b->keluhan ?: '-',
                'status_booking' => $b->status,
            ];
        }

        return [
            'status'  => 'success',
            'total'   => count($records),
            'records' => $records,
        ];
    }
}
