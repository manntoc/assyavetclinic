<?php

namespace App\AI;

use App\AI\Contracts\AIProviderInterface;
use Illuminate\Support\Facades\Log;

/**
 * QueryRewriterService : Model 1: Intent Router & Query Rewriter.
 *
 * Classifies user intent into RAG_DOCS | DB_SEARCH | CHITCHAT,
 * rewrites the user message into a standalone query, and extracts
 * the search entity for DB_SEARCH intents.
 *
 * Returns a structured array:
 *   ['intent' => string, 'standalone_query' => string, 'search_entity' => string|null]
 *
 * Falls back gracefully when no LLM provider is available.
 */
class QueryRewriterService
{
    /** Intent constants */
    public const INTENT_RAG      = 'RAG_DOCS';
    public const INTENT_DB       = 'DB_SEARCH';
    public const INTENT_CHITCHAT = 'CHITCHAT';

    private const SYSTEM_PROMPT = <<<'PROMPT'
Anda adalah Intent Router & Query Rewriter untuk sistem asisten internal klinik hewan Assyavet. Tugas Anda adalah menganalisis riwayat percakapan dan pesan terbaru staf, lalu menghasilkan output JSON terstruktur untuk menentukan alur kerja sistem.

# CLASSIFICATION CATEGORIES
1. `RAG_DOCS`: Pertanyaan seputar panduan penggunaan fitur, letak menu/tombol, alur kerja SOP, kebijakan, atau kendala/error sistem aplikasi.
2. `DB_SEARCH`: Permintaan pencarian atau laporan data dinamis dari database: mencari data pasien/klien, stok obat/barang, status transaksi/antrean, rekam medis, omzet, karyawan, layanan, estimasi biaya.
3. `CHITCHAT`: Sapaan, ucapan terima kasih, konfirmasi singkat, atau obrolan kasual yang tidak membutuhkan data teknis/sistem.

# RULES
1. Tulis ulang pesan user menjadi `standalone_query` yang lengkap, menyelesaikan kata ganti ("itu", "dia", "yang tadi", "pasien tersebut") berdasarkan riwayat chat.
2. Untuk CHITCHAT, `standalone_query` diisi "NONE".
3. `search_entity` diisi nama entitas jika DB_SEARCH (pilihan: patient, transaction, inventory, appointment, omzet, staff, layanan, invoice), atau null jika bukan DB_SEARCH.
4. Output WAJIB berupa raw JSON valid tanpa blok markdown atau teks tambahan.

# OUTPUT FORMAT (JSON ONLY)
{"intent": "RAG_DOCS" | "DB_SEARCH" | "CHITCHAT", "standalone_query": "string atau NONE", "search_entity": "string atau null"}

# EXAMPLES
---
History: []
User: "Gimana cara bikin rekam medis baru?"
Output: {"intent": "RAG_DOCS", "standalone_query": "cara pendaftaran pasien dan input rekam medis baru di Assyavet", "search_entity": null}

---
History: [User: "Saya cari riwayat kunjungan Kucing Mochi", AI: "Mochi pemiliknya Pak Budi ya?"]
User: "Iya betul, tolong cekin tanggal periksa terakhirnya"
Output: {"intent": "DB_SEARCH", "standalone_query": "tanggal kunjungan periksa terakhir kucing Mochi pemilik Pak Budi", "search_entity": "patient"}

---
History: [User: "berapa omzet bulan januari?", AI: "Omzet Januari 2026: Rp 12.500.000"]
User: "bandingkan dengan bulan februari"
Output: {"intent": "DB_SEARCH", "standalone_query": "bandingkan omzet bulan januari 2026 vs februari 2026", "search_entity": "omzet"}

---
History: [User: "Oke terima kasih infonya!"]
User: "Sip mantap"
Output: {"intent": "CHITCHAT", "standalone_query": "NONE", "search_entity": null}
PROMPT;

    protected ?AIProviderInterface $provider;

    public function __construct(?AIProviderInterface $provider = null)
    {
        $this->provider = $provider;
    }

    /**
     * Classify + rewrite user message.
     *
     * @param  string  $userMessage   Raw latest message from user
     * @param  array   $history       [['role'=>'user'|'assistant', 'content'=>'...'], ...]
     * @return array{intent: string, standalone_query: string, search_entity: string|null}
     */
    public function classify(string $userMessage, array $history = []): array
    {
        $default = [
            'intent'           => self::INTENT_DB,
            'standalone_query' => $userMessage,
            'search_entity'    => null,
        ];

        // Fast-path: no LLM available → treat as DB_SEARCH with raw message
        if ($this->provider === null || !$this->provider->checkHealth()) {
            return $default;
        }

        // Build transcript of recent history (last 6 turns max)
        $recentHistory = array_slice($history, -6);
        $transcript    = '';
        foreach ($recentHistory as $msg) {
            $role    = ucfirst($msg['role'] ?? 'user');
            $content = strip_tags($msg['content'] ?? '');
            $transcript .= "{$role}: {$content}\n";
        }

        $historyLine = $transcript ? "Chat History:\n{$transcript}\n" : '';
        $userPrompt  = "{$historyLine}User: {$userMessage}";

        try {
            $response = $this->provider->chatCompletion([
                ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                ['role' => 'user',   'content' => $userPrompt],
            ]);

            $raw = trim($response['choices'][0]['message']['content'] ?? '');

            if (empty($raw)) {
                return $default;
            }

            // Strip markdown fences if model wraps in ```json ... ```
            $raw = preg_replace('/^```(?:json)?\s*/i', '', $raw);
            $raw = preg_replace('/\s*```$/', '', $raw);

            $parsed = json_decode(trim($raw), true);

            if (!is_array($parsed) || empty($parsed['intent'])) {
                Log::warning("QueryRewriter: invalid JSON response → [{$raw}]");
                return $default;
            }

            $intent = strtoupper(trim($parsed['intent']));
            if (!in_array($intent, [self::INTENT_RAG, self::INTENT_DB, self::INTENT_CHITCHAT], true)) {
                $intent = self::INTENT_DB;
            }

            $standaloneQuery = trim($parsed['standalone_query'] ?? $userMessage);
            if (empty($standaloneQuery)) {
                $standaloneQuery = $userMessage;
            }

            $result = [
                'intent'           => $intent,
                'standalone_query' => $standaloneQuery,
                'search_entity'    => $parsed['search_entity'] ?? null,
            ];

            Log::debug('QueryRewriter classify', [
                'input'  => $userMessage,
                'output' => $result,
            ]);

            return $result;

        } catch (\Throwable $e) {
            Log::warning('QueryRewriter failed: ' . $e->getMessage());
            return $default;
        }
    }

    /**
     * Legacy compatibility: return standalone query string (or "NONE" for chitchat).
     * Used by code that hasn't migrated to classify() yet.
     */
    public function rewrite(string $userMessage, array $history = []): string
    {
        // Fast-path: no history → already standalone
        if (empty($history)) {
            return $userMessage;
        }

        $result = $this->classify($userMessage, $history);

        if ($result['intent'] === self::INTENT_CHITCHAT) {
            return 'NONE';
        }

        return $result['standalone_query'];
    }

    /**
     * Check if the rewritten query signals no retrieval is needed.
     */
    public static function isNone(string $rewrittenQuery): bool
    {
        return strtoupper(trim($rewrittenQuery)) === 'NONE';
    }
}
