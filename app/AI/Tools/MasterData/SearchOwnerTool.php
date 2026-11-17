<?php

namespace App\AI\Tools\MasterData;

use App\AI\Contracts\AIToolInterface;
use App\Models\Owner;
use App\Models\User;

class SearchOwnerTool implements AIToolInterface
{
    public function getName(): string
    {
        return 'search_owner';
    }

    public function getDescription(): string
    {
        return 'Cari data Owner/Pemilim hewan berdasarkan nama, telepon, atau nama hewan peliharaan.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    'description' => 'Nama owner, nomor telepon, atau nama hewan peliharaan'
                ]
            ],
            'required' => ['query']
        ];
    }

    public function authorize(User $user): bool
    {
        return true; // All authenticated & guest staff can search owners
    }

    public function requiresConfirmation(): bool
    {
        return false;
    }

    public function execute(array $parameters, User $user): array
    {
        $queryStr = trim($parameters['query'] ?? '');

        // Query Master Data Owner dengan eager-loaded pets
        $ownerQuery = Owner::with('pets');

        if (!empty($queryStr)) {
            $ownerQuery->where(function ($q) use ($queryStr) {
                $q->where('nama', 'LIKE', "%{$queryStr}%")
                  ->orWhere('telepon', 'LIKE', "%{$queryStr}%")
                  ->orWhereHas('pets', function ($pq) use ($queryStr) {
                      $pq->where('nama', 'LIKE', "%{$queryStr}%")
                         ->orWhere('jenis_hewan', 'LIKE', "%{$queryStr}%")
                         ->orWhere('ras_hewan', 'LIKE', "%{$queryStr}%");
                  });
            });
        }

        $owners = $ownerQuery->latest('id')->take(5)->get();

        $results = [];
        foreach ($owners as $o) {
            $petsNames  = [];
            $petsDetail = [];
            if ($o->pets && $o->pets->isNotEmpty()) {
                foreach ($o->pets as $p) {
                    $petsNames[]  = $p->nama;
                    $petsDetail[] = [
                        'nama'  => $p->nama,
                        'jenis' => $p->jenis_hewan ?: ($p->spesies ?: 'Hewan'),
                        'ras'   => $p->ras_hewan ?: '',
                    ];
                }
            }

            $results[] = [
                'id'          => $o->id,
                'nama'        => $o->nama,
                'telepon'     => $o->telepon ?: '-',
                'alamat'      => $o->alamat ?: '-',
                'pets'        => $petsNames,
                'pets_detail' => $petsDetail,
                'url'         => route('owner.show', $o->id),
            ];
        }

        // Fallback: jika di tabel owners belum ada hasil dan ada queryStr, cari di tabel bookings
        if (empty($results) && !empty($queryStr)) {
            $bookings = \App\Models\booking::where('nama', 'LIKE', "%{$queryStr}%")
                ->orWhere('nama_hewan', 'LIKE', "%{$queryStr}%")
                ->orWhere('telpon', 'LIKE', "%{$queryStr}%")
                ->latest('tanggal')
                ->take(5)
                ->get();

            if ($bookings->isNotEmpty()) {
                $grouped = $bookings->groupBy('nama');
                foreach ($grouped as $ownerName => $bList) {
                    $first    = $bList->first();
                    $petNames = $bList->pluck('nama_hewan')->unique()->filter()->values()->toArray();
                    $pDetails = [];
                    foreach ($petNames as $pName) {
                        $pDetails[] = [
                            'nama'  => $pName,
                            'jenis' => $first->jenis_hewan ?: 'Hewan',
                            'ras'   => '',
                        ];
                    }
                    $results[] = [
                        'id'          => $first->owner_id,
                        'nama'        => $ownerName,
                        'telepon'     => $first->telpon ?: '-',
                        'alamat'      => $first->alamat ?: '-',
                        'pets'        => $petNames,
                        'pets_detail' => $pDetails,
                        'url'         => route('booking-uncompleted'),
                    ];
                }
            }
        }

        if (empty($results)) {
            $msg = !empty($queryStr)
                ? "Data owner/customer untuk keyword '{$queryStr}' tidak ditemukan."
                : "Belum ada data customer terdaftar di sistem.";
            return ['found' => false, 'message' => $msg, 'owners' => []];
        }

        return ['found' => true, 'total' => count($results), 'owners' => $results];
    }
}
