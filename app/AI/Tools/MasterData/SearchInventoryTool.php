<?php

namespace App\AI\Tools\MasterData;

use App\AI\Contracts\AIToolInterface;
use App\Models\Inventori;
use App\Models\User;

class SearchInventoryTool implements AIToolInterface
{
    public function getName(): string
    {
        return 'search_inventory';
    }

    public function getDescription(): string
    {
        return 'Cari sisa stok obat, alkes, atau barang farmasi di inventori klinik.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    'description' => 'Nama obat, barang, atau alkes'
                ]
            ],
            'required' => ['query']
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
        $rawQuery = trim($parameters['query'] ?? '');
        
        // Strip noise & species words (kucing, anjing, hewan, pet, pasien, etc.)
        $cleanQuery = $this->cleanMedicineQueryNoise($rawQuery);

        $targetSearch = !empty($cleanQuery) ? $cleanQuery : $rawQuery;

        // 1. Try Exact Phrase Match
        $invs = Inventori::where('nama_barang', 'LIKE', "%{$targetSearch}%")
            ->orderBy('jumlah', 'asc')
            ->take(6)
            ->get();

        // 2. Try Word-by-Word OR Match (Tokenized Fuzzy)
        if ($invs->isEmpty()) {
            $words = explode(' ', $targetSearch);
            $meaningfulWords = array_filter($words, function($w) {
                return strlen($w) >= 3;
            });

            if (!empty($meaningfulWords)) {
                $invs = Inventori::where(function($q) use ($meaningfulWords) {
                    foreach ($meaningfulWords as $w) {
                        $q->orWhere('nama_barang', 'LIKE', "%{$w}%");
                    }
                })->orderBy('jumlah', 'asc')->take(6)->get();
            }
        }

        // 3. Fallback: Levenshtein / String Similarity Check if DB has items
        if ($invs->isEmpty() && strlen($targetSearch) >= 3) {
            $allItems = Inventori::select('id', 'nama_barang', 'jumlah', 'harga')->get();
            $bestMatches = [];

            foreach ($allItems as $item) {
                $sim = 0;
                similar_text(strtolower($targetSearch), strtolower($item->nama_barang), $sim);
                if ($sim >= 40) { // 40% similarity threshold
                    $bestMatches[] = ['item' => $item, 'sim' => $sim];
                }
            }

            if (!empty($bestMatches)) {
                usort($bestMatches, function($a, $b) {
                    return $b['sim'] <=> $a['sim'];
                });
                $invs = collect(array_column(array_slice($bestMatches, 0, 6), 'item'));
            }
        }

        if ($invs->isEmpty()) {
            return ['found' => false, 'message' => "Obat/barang '{$targetSearch}' tidak ditemukan di inventori (Stok: 0)."];
        }

        $results = [];
        foreach ($invs as $item) {
            $statusText = $item->jumlah > 10 ? 'Tersedia' : ($item->jumlah > 0 ? 'Stok Tipis' : 'Habis');
            $results[] = [
                'id' => $item->id,
                'nama_barang' => $item->nama_barang,
                'stok' => $item->jumlah,
                'harga' => $item->harga,
                'harga_formatted' => 'Rp ' . number_format($item->harga, 0, ',', '.'),
                'status' => $statusText,
                'url' => route('inventori')
            ];
        }

        return ['found' => true, 'total' => count($results), 'items' => $results];
    }

    protected function cleanMedicineQueryNoise(string $query): string
    {
        $noiseWords = [
            'siapa', 'pemilik', 'owner', 'punya', 'milik', 'kepunyaan', 'dari', 'yang', 'saya', 'dengan',
            'ada', 'cari', 'tolong', 'bisa', 'minta', 'data', 'apa', 'bagaimana', 'cara', 'cek', 'lihat',
            'tentang', 'tampilkan', 'dong', 'kak', 'min', 'stok', 'stoknya', 'obat', 'inventori', 'apotek',
            'farmasi', 'layanan', 'tarif', 'biaya', 'ya', 'kah', 'sisa', 'apakah', 'banyak', 'masih', 'dosis',
            'jumlah', 'detail', 'kasih', 'tahu', 'saja', 'berapa', 'catatn', 'catatan', 'pengambilan',
            // Species / Patient noise words
            'kucing', 'anjing', 'hewan', 'pet', 'pasien', 'kelinci', 'hamster', 'burung', 'otter', 'reptil', 'musang'
        ];

        $pattern = '/\b(' . implode('|', $noiseWords) . ')\b/i';
        $clean = trim(preg_replace($pattern, '', $query));
        $clean = preg_replace('/[^\w\s]/u', '', $clean);
        return trim(preg_replace('/\s+/', ' ', $clean));
    }
}
