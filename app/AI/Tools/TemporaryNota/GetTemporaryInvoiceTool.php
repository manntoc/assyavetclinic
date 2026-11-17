<?php

namespace App\AI\Tools\TemporaryNota;

use App\AI\Contracts\AIToolInterface;
use App\Models\EstimasiBiaya;
use App\Models\User;

class GetTemporaryInvoiceTool implements AIToolInterface
{
    public function getName(): string
    {
        return 'get_temporary_invoice';
    }

    public function getDescription(): string
    {
        return 'Lihat data Nota Estimasi Biaya Pre-Billing (Non-billing, Zero Stock Mutation).';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    'description' => 'Nama owner, nama hewan, atau kode estimasi'
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
        $queryStr = trim($parameters['query'] ?? '');

        $query = EstimasiBiaya::query();

        if (!empty($queryStr)) {
            $query->where('nama_owner', 'LIKE', "%{$queryStr}%")
                ->orWhere('nama_hewan', 'LIKE', "%{$queryStr}%")
                ->orWhere('kode_estimasi', 'LIKE', "%{$queryStr}%");
        }

        $estimations = $query->latest()->take(5)->get();

        if ($estimations->isEmpty()) {
            return ['found' => false, 'message' => 'Nota estimasi biaya pre-billing tidak ditemukan.'];
        }

        $results = [];
        foreach ($estimations as $e) {
            $results[] = [
                'id' => $e->id,
                'kode_estimasi' => $e->kode_estimasi,
                'nama_owner' => $e->nama_owner,
                'nama_hewan' => $e->nama_hewan,
                'grand_total' => $e->grand_total,
                'grand_total_formatted' => 'Rp ' . number_format($e->grand_total, 0, ',', '.'),
                'url' => route('estimasi-biaya.index')
            ];
        }

        return ['found' => true, 'total' => count($results), 'invoices' => $results];
    }
}
