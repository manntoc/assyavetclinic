<?php

namespace App\Services\Instagram;

use App\Models\booking;
use App\Models\layanan;
use App\Models\Inventori;

/**
 * Resolves content source data from existing clinic tables.
 * All patient data is anonymized before returning.
 * No new DB tables. Read-only.
 */
class InstagramSourceResolver
{
    /**
     * Return list of available source options for the UI.
     */
    public static function sourceTypes(): array
    {
        return [
            'kasus_klinik' => 'Kasus Klinik (anonymized)',
            'edukasi'      => 'Konten Edukasi',
            'layanan'      => 'Layanan Klinik',
            'promo'        => 'Promo & Penawaran',
            'custom'       => 'Topik Kustom',
        ];
    }

    /**
     * Return structured source data for AI prompt based on source_type.
     * All personally identifiable information is stripped.
     */
    public static function resolve(string $sourceType, ?int $sourceId = null): array
    {
        return match ($sourceType) {
            'kasus_klinik' => self::resolveKasusKlinik($sourceId),
            'layanan'      => self::resolveLayanan($sourceId),
            'inventori'    => self::resolveInventori($sourceId),
            default        => ['type' => $sourceType, 'data' => []],
        };
    }

    // --- Kasus Klinik (Anonymized) ---

    public static function resolveKasusKlinik(?int $bookingId = null): array
    {
        if ($bookingId) {
            $booking = booking::find($bookingId);
            if (!$booking) return ['type' => 'kasus_klinik', 'data' => []];
            return ['type' => 'kasus_klinik', 'data' => [self::anonymizeBooking($booking)]];
        }

        // Return list of completed cases for selection
        $bookings = booking::where('status', 'Telah diselesaikan')
            ->whereNotNull('assessment')
            ->where('assessment', '!=', '')
            ->latest()
            ->take(20)
            ->get();

        return [
            'type' => 'kasus_klinik',
            'data' => $bookings->map(fn($b) => self::anonymizeBooking($b))->toArray(),
        ];
    }

    /**
     * Strip all PII. Only keep medical/clinical facts.
     */
    private static function anonymizeBooking(booking $b): array
    {
        return [
            'jenis_hewan'    => $b->jenis_hewan ?? $b->jenishewan ?? 'Hewan Peliharaan',
            'spesies'        => $b->ownerPet?->spesies ?? null,
            'keluhan'        => $b->keluhan,
            'assessment'     => $b->assessment,
            'subjective'     => $b->subjective,
            'objective'      => $b->objective,
            'plan'           => $b->plan,
            'suhu_tubuh'     => $b->suhu_tubuh,
            'denyut_jantung' => $b->denyut_jantung,
            'layanan'        => self::extractLayananFromBooking($b),
            'tanggal'        => $b->tanggal,
            // PII stripped: nama, telpon, alamat, owner info, kode_booking
        ];
    }

    private static function extractLayananFromBooking(booking $b): array
    {
        $services = [];
        $fields = ['jasa_dokter', 'vaksin', 'grooming', 'operasi', 'lainnya', 'diagnosa_penunjang', 'transportasi'];
        foreach ($fields as $f) {
            if (!empty($b->$f)) $services[] = $b->$f;
        }
        return $services;
    }

    // --- Layanan ---

    public static function resolveLayanan(?int $layanantId = null): array
    {
        if ($layanantId) {
            $l = layanan::find($layanantId);
            return ['type' => 'layanan', 'data' => $l ? [['nama' => $l->nama, 'jenis' => $l->jenis, 'harga' => $l->harga]] : []];
        }

        $all = layanan::all(['id', 'nama', 'jenis', 'harga']);
        return ['type' => 'layanan', 'data' => $all->toArray()];
    }

    // --- Inventori / Obat ---

    public static function resolveInventori(?int $itemId = null): array
    {
        if ($itemId) {
            $item = Inventori::find($itemId);
            return ['type' => 'inventori', 'data' => $item ? [['nama_barang' => $item->nama_barang]] : []];
        }

        $all = Inventori::all(['id', 'nama_barang']);
        return ['type' => 'inventori', 'data' => $all->toArray()];
    }

    /**
     * For UI: return bookings eligible as content sources (has assessment).
     */
    public static function getSelectableBookings(): \Illuminate\Database\Eloquent\Collection
    {
        return booking::where('status', 'Telah diselesaikan')
            ->whereNotNull('assessment')
            ->where('assessment', '!=', '')
            ->select('id', 'jenis_hewan', 'assessment', 'keluhan', 'tanggal')
            ->latest()
            ->take(50)
            ->get();
    }

    public static function getSelectableLayanan(): \Illuminate\Database\Eloquent\Collection
    {
        return layanan::all(['id', 'nama', 'jenis']);
    }
}
