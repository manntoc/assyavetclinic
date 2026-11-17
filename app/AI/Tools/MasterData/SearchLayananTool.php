<?php

namespace App\AI\Tools\MasterData;

use App\AI\Contracts\AIToolInterface;
use App\Models\layanan;
use App\Models\User;

class SearchLayananTool implements AIToolInterface
{
    public function getName(): string
    {
        return 'search_layanan';
    }

    public function getDescription(): string
    {
        return 'Cari tarif dan daftar layanan medis klinik (jasa dokter, vaksin, grooming, operasi, dll).';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    'description' => 'Nama layanan atau jenis layanan (opsional, kosong = semua layanan)'
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
        $query = trim($parameters['query'] ?? '');

        $q = layanan::query()->orderBy('jenis')->orderBy('harga');

        if (!empty($query)) {
            $q->where(function ($sq) use ($query) {
                $sq->where('nama', 'LIKE', "%{$query}%")
                   ->orWhere('jenis', 'LIKE', "%{$query}%");
            });
        }

        $layananList = $q->take(20)->get();

        if ($layananList->isEmpty()) {
            return [
                'found'   => false,
                'message' => empty($query)
                    ? 'Tidak ada layanan terdaftar.'
                    : "Layanan dengan keyword '{$query}' tidak ditemukan.",
            ];
        }

        $items = $layananList->map(fn($l) => [
            'nama'   => $l->nama,
            'jenis'  => $l->jenis,
            'harga'  => $l->harga,
            'harga_formatted' => 'Rp ' . number_format($l->harga, 0, ',', '.'),
        ])->values()->toArray();

        // Group by jenis for structured output
        $grouped = [];
        foreach ($items as $item) {
            $grouped[$item['jenis']][] = $item;
        }

        return [
            'found'   => true,
            'total'   => count($items),
            'query'   => $query ?: 'semua',
            'grouped' => $grouped,
            'items'   => $items,
        ];
    }
}
