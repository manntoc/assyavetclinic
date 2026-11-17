<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Owner;
use App\Models\OwnerPet;
use App\Models\booking;
use App\Models\Inventori;
use App\Models\layanan;
use App\Models\PetDailyLog;
use App\Models\EstimasiBiaya;
use App\Models\User;
use App\Models\Transaksi;
use App\Services\NineRouterService;
use App\AI\AIService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class AIAssistantController extends Controller
{
    protected NineRouterService $nineRouter;
    protected AIService $aiService;

    public function __construct(NineRouterService $nineRouter, AIService $aiService)
    {
        $this->nineRouter = $nineRouter;
        $this->aiService = $aiService;
    }

    /**
     * Handle incoming conversational AI Assistant chat query.
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'current_path' => 'nullable|string',
            'conversation_id' => 'nullable|integer',
        ]);

        $rawMsg = trim($request->input('message'));
        $lowerMsg = trim(preg_replace('/[^\w\s]/u', '', strtolower($rawMsg)));
        $currentPath = $request->input('current_path', '/');
        $conversationId = $request->input('conversation_id');
        $user = Auth::user();

        try {
            // 0. Check for explicit Out-of-Context non-clinic topics
            if ($this->isExplicitOutOfContextTopic($lowerMsg)) {
                return response()->json($this->buildOutOfContextResponse());
            }

            // 1. Process via Modular AI Architecture (AIService & AIOrchestrator)
            $aiResponse = $this->aiService->ask($rawMsg, $user, $conversationId);
            if (!empty($aiResponse) && isset($aiResponse['reply'])) {
                return response()->json(array_merge([
                    'status' => 'success',
                    'suggestions' => $this->generateSuggestions($lowerMsg, [])
                ], $aiResponse));
            }

            // 2. Fallback to Local NLU Engine if needed
            $nluContext = $this->analyzeIntentAndRetrieveContext($rawMsg, $lowerMsg);
            $guideContext = $this->retrieveFeatureGuideContext($lowerMsg);

            return response()->json($this->buildSmartFallbackResponse($rawMsg, $lowerMsg, $nluContext, $guideContext));

        } catch (Exception $e) {
            Log::error('AI Assistant Chat Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'reply' => 'Terjadi kesalahan sistem. Silakan coba pertanyaan lain.',
                'suggestions' => ['Stok obat Acidurin Vet', 'Booking hari ini', 'Laporan Omzet']
            ], 500);
        }
    }

    /**
     * Detect explicit non-clinic / out-of-context topics strictly.
     */
    protected function isExplicitOutOfContextTopic(string $lowerMsg): bool
    {
        $outOfContextKeywords = [
            'presiden', 'politik', 'pemilu', 'cuaca', 'hujan', 'ramalan', 'zodiak',
            'masak', 'resep makanan', 'kue bolu', 'liga', 'sepak bola', 'pemain bola',
            'klub bola', 'lirik lagu', 'lagu', 'sinetron', 'artis', 'gameplay',
            'jokes', 'lelucon', 'saham', 'crypto', 'bitcoin', 'dolar', 'pemerintah'
        ];

        foreach ($outOfContextKeywords as $keyword) {
            if (str_contains($lowerMsg, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Build concise, minimal response for Out-of-Context queries.
     */
    protected function buildOutOfContextResponse(): array
    {
        $outOfContextText = "Maaf, pertanyaan Anda di luar operasional klinik Assyavet OS 🩺<br>" .
                            "Saya khusus membantu data **Owner, Booking, Rawat Inap, Stok Obat, Tarif, & Omzet Klinik**.";

        return [
            'status' => 'out_of_context',
            'reply' => $outOfContextText,
            'type' => 'out_of_context',
            'suggestions' => [
                'Stok obat Acidurin Vet',
                'Siapa pemilik kucing Milo?',
                'Booking hari ini',
                'Laporan Omzet'
            ]
        ];
    }

    /**
     * Analyze user conversation intent and extract entities across ALL Assyavet OS models.
     */
    protected function analyzeIntentAndRetrieveContext(string $rawMsg, string $lowerMsg): array
    {
        $context = [
            'intent' => 'general_search',
            'direct_reply' => null,
            'summary' => [],
            'cards' => [],
            'action_url' => null,
            'action_label' => null,
            'found_data' => false,
            'category_type' => null
        ];

        // INVENTORY INTENT
        if (str_contains($lowerMsg, 'stok') || str_contains($lowerMsg, 'obat') || str_contains($lowerMsg, 'sisa') || str_contains($lowerMsg, 'inventori')) {
            $cleanMedicine = $this->cleanQueryNoise($lowerMsg, ['cari', 'stok', 'stoknya', 'obat', 'sisa', 'jumlah', 'ada', 'berapa', 'banyak', 'masih', 'cek', 'apakah', 'tolong', 'inventori', 'apotek', 'farmasi']);
            $queryInv = Inventori::query();
            if (!empty($cleanMedicine) && strlen($cleanMedicine) >= 2) {
                $queryInv->where('nama_barang', 'LIKE', "%{$cleanMedicine}%");
            }
            $invResults = $queryInv->orderBy('jumlah', 'asc')->take(6)->get();

            if ($invResults->count() > 0) {
                $context['found_data'] = true;
                $context['category_type'] = 'inventori';
                $replies = [];
                foreach ($invResults as $inv) {
                    $priceFormatted = 'Rp ' . number_format($inv->harga, 0, ',', '.');
                    $statusText = $inv->jumlah > 10 ? 'Tersedia' : ($inv->jumlah > 0 ? 'Stok Tipis' : 'Habis');
                    $replies[] = "• **{$inv->nama_barang}** | Stok: **{$inv->jumlah}** | Harga: **{$priceFormatted}** [{$statusText}]";
                    $context['cards'][] = [
                        'title' => $inv->nama_barang,
                        'subtitle' => "Harga: {$priceFormatted} | Stok: <strong>{$inv->jumlah}</strong> | Status: {$statusText}",
                        'badge' => $statusText,
                        'badge_class' => $inv->jumlah > 10 ? 'bg-success' : 'bg-warning text-dark',
                        'url' => route('inventori')
                    ];
                }
                $context['direct_reply'] = "Stok **Inventori & Obat Apotek**:<br>" . implode("<br>", $replies);
                $context['action_url'] = route('inventori');
                $context['action_label'] = 'Buka Stok Inventori';
                return $context;
            }
        }

        return $context;
    }

    protected function cleanQueryNoise(string $msg, array $extraNoise = []): string
    {
        $defaultNoise = ['siapa', 'pemilik', 'owner', 'punya', 'milik', 'kepunyaan', 'dari', 'yang', 'saya', 'dengan', 'ada', 'cari', 'tolong', 'bisa', 'minta', 'data', 'apa', 'bagaimana', 'cara', 'cek', 'lihat', 'tentang', 'tampilkan', 'dong', 'kak', 'min', 'stok', 'stoknya', 'bookingan', 'antrean', 'pasien', 'hewan', 'pet', 'obat', 'inventori', 'layanan', 'tarif', 'alur', 'queue', 'omzet', 'laporan', 'estimasi', 'biaya', 'bernama', 'kucing', 'anjing', 'kelinci', 'hamster', 'burung', 'ya', 'kah', 'sisa', 'apakah', 'banyak', 'masih', 'dosis', 'jumlah', 'detail', 'kasih', 'tahu', 'saja', 'berapa'];
        $allNoise = array_unique(array_merge($defaultNoise, $extraNoise));
        $pattern = '/\b(' . implode('|', $allNoise) . ')\b/i';
        $clean = trim(preg_replace($pattern, '', $msg));
        $clean = preg_replace('/[^\w\s]/u', '', $clean);
        return trim(preg_replace('/\s+/', ' ', $clean));
    }

    protected function retrieveFeatureGuideContext(string $lowerMsg): ?string
    {
        if (str_contains($lowerMsg, 'alur') || str_contains($lowerMsg, 'queue') || str_contains($lowerMsg, 'stage') || str_contains($lowerMsg, 'proses booking')) {
            return "ALUR 5+1 QUEUE STAGE ASSYAVET OS:\n" .
                   "1. Kedatangan (Registrasi Owner & Pasien)\n" .
                   "2. Memulai (Pemeriksaan Dokter & EMR SOAP)\n" .
                   "3. Rawat Inap (Catatan Harian Daily Log)\n" .
                   "4. Apotek (Resep Obat & Potong Stok)\n" .
                   "5. Pembayaran (POS Checkout & Billing)\n" .
                   "6. Selesai (Nota PDF)";
        }
        return null;
    }

    protected function buildSmartFallbackResponse(string $rawMsg, string $lowerMsg, array $nluContext, ?string $guideContext): array
    {
        if (!empty($nluContext['direct_reply'])) {
            return [
                'status' => 'success',
                'reply' => $nluContext['direct_reply'],
                'type' => 'conversational_reply',
                'results' => $nluContext['cards'],
                'action_url' => $nluContext['action_url'],
                'action_label' => $nluContext['action_label'],
                'suggestions' => $this->generateSuggestions($lowerMsg, $nluContext)
            ];
        }

        return $this->buildOutOfContextResponse();
    }

    protected function generateSuggestions(string $lowerMsg, array $nluContext): array
    {
        return ['Stok obat Acidurin Vet', 'Siapa pemilik kucing Milo?', 'Booking hari ini', 'Laporan Omzet'];
    }
}
