<?php

namespace App\AI;

use App\AI\Contracts\AIProviderInterface;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Exception;
use App\AI\AIKnowledgeBase;
use App\AI\AIKnowledgeDocs;
use App\AI\AISkillManager;

class AIOrchestrator
{
    protected AIProviderInterface $provider;
    protected AIContextManager $contextManager;
    protected AIToolRegistry $toolRegistry;
    protected AIToolExecutor $toolExecutor;
    protected QueryRewriterService $queryRewriter;

    /** @var AIProviderInterface[] */
    protected array $fallbackProviders = [];

    public function __construct(
        AIProviderInterface $provider,
        AIContextManager $contextManager,
        AIToolRegistry $toolRegistry,
        AIToolExecutor $toolExecutor
    ) {
        $this->provider       = $provider;
        $this->contextManager = $contextManager;
        $this->toolRegistry   = $toolRegistry;
        $this->toolExecutor   = $toolExecutor;
        // QueryRewriter uses the same primary provider; initialised lazily after setFallbackProviders if needed
        $this->queryRewriter  = new QueryRewriterService(null); // provider injected after health check
    }

    /**
     * Register additional providers used as fallback when primary is down.
     *
     * @param AIProviderInterface[] $providers
     */
    public function setFallbackProviders(array $providers): void
    {
        $this->fallbackProviders = $providers;
    }

    /**
     * Process user chat message through AI Orchestration Pipeline.
     */
    public function processMessage(string $userMsg, ?User $user, ?int $conversationId = null): array
    {
        // 1. Get/Create conversation & context
        $conversation = $this->contextManager->getOrCreateConversation($user, $conversationId);

        // 2. Check Slash Commands (/karyawan, /owner, /booking, /emr, /inventori, /laporan, /layanan, /general, /help, /skills)
        $cmdParsed = AISkillManager::parseCommand($userMsg);
        if ($cmdParsed['is_command']) {
            $this->contextManager->addMessage('user', $userMsg);

            if ($cmdParsed['action'] === 'reset') {
                $this->contextManager->setActiveSkill(null);
                $this->contextManager->resetContextData(false);
                $resp = AISkillManager::buildResetResponse($conversation->id);
                $this->contextManager->addMessage('assistant', $resp['reply']);
                return $this->wrapResponseWithSkill($resp);
            }

            if ($cmdParsed['action'] === 'help') {
                $resp = AISkillManager::buildHelpResponse($conversation->id);
                $this->contextManager->addMessage('assistant', $resp['reply']);
                return $this->wrapResponseWithSkill($resp);
            }

            if ($cmdParsed['action'] === 'switch' && $cmdParsed['skill']) {
                $skill = $cmdParsed['skill'];
                $this->contextManager->setActiveSkill($skill['key']);

                if (empty($cmdParsed['remainder'])) {
                    $resp = AISkillManager::buildSwitchResponse($skill, $conversation->id);
                    $this->contextManager->addMessage('assistant', $resp['reply']);
                    return $this->wrapResponseWithSkill($resp);
                }

                // If user entered command with query (e.g. "/karyawan siapa dokter jaga?")
                $userMsg = $cmdParsed['remainder'];
            }
        } else {
            $this->contextManager->addMessage('user', $userMsg);
        }

        // 3. Normalize typos & synonyms
        $normalizedMsg = $this->normalizeTyposAndSynonyms($userMsg);

        // Entity Memory: extract entities from user message → store in context_data['entities']
        $this->contextManager->extractAndStoreEntities($userMsg);

        $lowerMsg = trim(preg_replace('/[^\w\s]/u', '', strtolower($normalizedMsg)));

        // 2. Check pending confirmation state
        if ($conversation->state === AIStateMachine::WAITING_CONFIRMATION) {
            $contextData = $conversation->context_data ?? [];
            $pendingTool = $contextData['pending_tool'] ?? null;

            if ($this->isConfirmationAffirmative($lowerMsg)) {
                if ($pendingTool) {
                    $this->contextManager->updateState(AIStateMachine::EXECUTING);
                    $toolObj = $this->toolRegistry->getTool($pendingTool['name']);

                    if ($toolObj) {
                        $execResult = $this->toolExecutor->execute($toolObj, $pendingTool['parameters'] ?? [], $user, $conversation->id);
                        $this->contextManager->updateState(AIStateMachine::COMPLETED);
                        $this->contextManager->resetContextData();

                        if ($execResult['status'] === 'success') {
                            $data  = $execResult['data'];
                            $reply = "Booking berhasil dibuat untuk Pasien **" . ($data['pet'] ?? 'Hewan') . "** (Owner: " . ($data['owner'] ?? 'Pemilik') . "). Kode: **" . ($data['kode_booking'] ?? '-') . "**.";
                        } else {
                            $reply = "Gagal membuat booking: " . ($execResult['message'] ?? 'Terjadi kesalahan.');
                        }

                        $this->contextManager->addMessage('assistant', $reply);
                        return $this->wrapResponseWithSkill([
                            'status'          => 'success',
                            'reply'           => $reply,
                            'conversation_id' => $conversation->id,
                            'state'           => AIStateMachine::COMPLETED,
                        ]);
                    }
                }
            } elseif ($this->isConfirmationNegative($lowerMsg)) {
                $this->contextManager->updateState(AIStateMachine::IDLE);
                $this->contextManager->resetContextData();
                $reply = "Tindakan telah dibatalkan atas permintaan Anda.";
                $this->contextManager->addMessage('assistant', $reply);
                return $this->wrapResponseWithSkill([
                    'status'          => 'success',
                    'reply'           => $reply,
                    'conversation_id' => $conversation->id,
                    'state'           => AIStateMachine::IDLE,
                ]);
            }
        }

        // 2b. Check pending clarification state (COLLECTING_DATA)
        if ($conversation->state === AIStateMachine::COLLECTING_DATA) {
            $contextData          = $conversation->context_data ?? [];
            $pendingClarification = $contextData['pending_clarification'] ?? null;

            if ($pendingClarification) {
                $cType = $pendingClarification['type'] ?? '';

                if ($this->isConfirmationNegative($lowerMsg)) {
                    $this->contextManager->updateState(AIStateMachine::IDLE);
                    $this->contextManager->resetContextData();
                    $reply = "Pencarian dibatalkan. Silakan tanyakan hal lain yang Anda butuhkan.";
                    $this->contextManager->addMessage('assistant', $reply);
                    return $this->wrapResponseWithSkill([
                        'status'          => 'success',
                        'reply'           => $reply,
                        'conversation_id' => $conversation->id,
                        'state'           => AIStateMachine::IDLE,
                    ]);
                }

                // Check if user is greeting or switched topic to a different domain
                $greetingWords = ['halo', 'hai', 'hi', 'hello', 'selamat pagi', 'selamat siang',
                                  'selamat sore', 'selamat malam', 'assalamu', 'permisi', 'hei', 'hey'];
                $isGreeting = false;
                foreach ($greetingWords as $g) {
                    if (str_contains($lowerMsg, $g)) { $isGreeting = true; break; }
                }

                $isTopicSwitch = $isGreeting;
                if (!$isTopicSwitch) {
                    if ($cType === 'owner_search') {
                        $isTopicSwitch = str_contains($lowerMsg, 'rekam medis')
                            || str_contains($lowerMsg, 'emr')
                            || str_contains($lowerMsg, 'soap')
                            || str_contains($lowerMsg, 'stok')
                            || str_contains($lowerMsg, 'obat')
                            || str_contains($lowerMsg, 'omzet')
                            || str_contains($lowerMsg, 'pendapatan')
                            || str_contains($lowerMsg, 'antrean')
                            || str_contains($lowerMsg, 'jadwal')
                            || str_contains($lowerMsg, 'sop')
                            || str_contains($lowerMsg, 'panduan');
                    } elseif ($cType === 'booking_detail' || $cType === 'emr') {
                        $isTopicSwitch = str_contains($lowerMsg, 'stok')
                            || str_contains($lowerMsg, 'obat')
                            || str_contains($lowerMsg, 'omzet')
                            || str_contains($lowerMsg, 'pendapatan')
                            || str_contains($lowerMsg, 'owner')
                            || str_contains($lowerMsg, 'pemilik')
                            || str_contains($lowerMsg, 'customer');
                    } elseif ($cType === 'inventory_search') {
                        $isTopicSwitch = str_contains($lowerMsg, 'rekam medis')
                            || str_contains($lowerMsg, 'emr')
                            || str_contains($lowerMsg, 'omzet')
                            || str_contains($lowerMsg, 'pendapatan')
                            || str_contains($lowerMsg, 'antrean')
                            || str_contains($lowerMsg, 'jadwal')
                            || str_contains($lowerMsg, 'owner')
                            || str_contains($lowerMsg, 'pemilik');
                    }
                }

                if ($isTopicSwitch) {
                    $this->contextManager->updateState(AIStateMachine::IDLE);
                    $this->contextManager->resetContextData();
                } elseif ($cType === 'owner_search') {
                    $isListRequest = str_contains($lowerMsg, 'terbaru')
                        || str_contains($lowerMsg, 'daftar')
                        || str_contains($lowerMsg, 'semua')
                        || str_contains($lowerMsg, 'list');
                    $searchParam = $isListRequest ? '' : $userMsg;

                    $toolObj = $this->toolRegistry->getTool('search_owner');
                    if ($toolObj) {
                        $this->contextManager->updateState(AIStateMachine::EXECUTING);
                        $execResult = $this->toolExecutor->execute($toolObj, ['query' => $searchParam], $user, $conversation->id);
                        $this->contextManager->updateState(AIStateMachine::COMPLETED);
                        $this->contextManager->resetContextData();

                        $formattedReply = $this->formatToolResultToNaturalResponse('search_owner', $execResult);
                        $this->contextManager->addMessage('assistant', $formattedReply['reply']);
                        return array_merge([
                            'status'          => 'success',
                            'conversation_id' => $conversation->id,
                            'state'           => AIStateMachine::COMPLETED,
                        ], $formattedReply);
                    }
                } elseif ($cType === 'booking_detail') {
                    $toolObj = $this->toolRegistry->getTool('get_booking_detail');
                    if ($toolObj) {
                        $kode = '';
                        if (preg_match('/\b(BOOK[A-Z0-9]{5,})\b/i', $userMsg, $m)) {
                            $kode = strtoupper($m[1]);
                        }
                        $this->contextManager->updateState(AIStateMachine::EXECUTING);
                        $execResult = $this->toolExecutor->execute($toolObj, [
                            'kode_booking' => $kode,
                            'owner'        => $kode ? '' : $userMsg,
                            'pet'          => $kode ? '' : $userMsg,
                        ], $user, $conversation->id);
                        $this->contextManager->updateState(AIStateMachine::COMPLETED);
                        $this->contextManager->resetContextData();

                        $formattedReply = $this->formatToolResultToNaturalResponse('get_booking_detail', $execResult);
                        $this->contextManager->addMessage('assistant', $formattedReply['reply']);
                        return array_merge([
                            'status'          => 'success',
                            'conversation_id' => $conversation->id,
                            'state'           => AIStateMachine::COMPLETED,
                        ], $formattedReply);
                    }
                } elseif ($cType === 'emr') {
                    $toolObj = $this->toolRegistry->getTool('get_emr');
                    if ($toolObj) {
                        $kode = '';
                        if (preg_match('/\b(BOOK[A-Z0-9]{5,})\b/i', $userMsg, $m)) {
                            $kode = strtoupper($m[1]);
                        }
                        $this->contextManager->updateState(AIStateMachine::EXECUTING);
                        $execResult = $this->toolExecutor->execute($toolObj, [
                            'kode_booking' => $kode,
                            'pet'          => $kode ? '' : $userMsg,
                            'limit'        => 3,
                        ], $user, $conversation->id);
                        $this->contextManager->updateState(AIStateMachine::COMPLETED);
                        $this->contextManager->resetContextData();

                        $formattedReply = $this->formatToolResultToNaturalResponse('get_emr', $execResult);
                        $this->contextManager->addMessage('assistant', $formattedReply['reply']);
                        return array_merge([
                            'status'          => 'success',
                            'conversation_id' => $conversation->id,
                            'state'           => AIStateMachine::COMPLETED,
                        ], $formattedReply);
                    }
                } elseif ($cType === 'inventory_search') {
                    $toolObj = $this->toolRegistry->getTool('search_inventory');
                    if ($toolObj) {
                        $this->contextManager->updateState(AIStateMachine::EXECUTING);
                        $execResult = $this->toolExecutor->execute($toolObj, ['query' => $userMsg], $user, $conversation->id);
                        $this->contextManager->updateState(AIStateMachine::COMPLETED);
                        $this->contextManager->resetContextData();

                        $formattedReply = $this->formatToolResultToNaturalResponse('search_inventory', $execResult);
                        $this->contextManager->addMessage('assistant', $formattedReply['reply']);
                        return array_merge([
                            'status'          => 'success',
                            'conversation_id' => $conversation->id,
                            'state'           => AIStateMachine::COMPLETED,
                        ], $formattedReply);
                    }
                }
            }
        }

        // 3. Greeting / chitchat handler (before NLU tool resolver)
        $greetingWords = ['halo', 'hai', 'hi', 'hello', 'selamat pagi', 'selamat siang',
                          'selamat sore', 'selamat malam', 'assalamu', 'permisi', 'hei', 'hey'];
        $isGreeting = false;
        foreach ($greetingWords as $g) {
            if (str_contains($lowerMsg, $g)) { $isGreeting = true; break; }
        }
        if ($isGreeting && strlen($lowerMsg) <= 40) {
            $hour = (int) now()->format('H');
            $waktu = $hour < 11 ? 'pagi' : ($hour < 15 ? 'siang' : ($hour < 19 ? 'sore' : 'malam'));
            $userName = $user ? $user->name : 'Staff';
            $reply = "Selamat {$waktu}, <strong>{$userName}</strong>! Saya <strong>VetClinic AI Assistant</strong> Assyavet.<br>"
                   . "Saya siap membantu Anda dengan:<br>"
                   . "- Antrean & booking pasien<br>"
                   . "- Data owner & hewan<br>"
                   . "- Stok obat & inventori<br>"
                   . "- Tarif layanan & laporan omzet<br>"
                   . "Silakan ketik pertanyaan Anda!";
            $this->contextManager->addMessage('assistant', $reply);
            return $this->wrapResponseWithSkill([
                'status'          => 'success',
                'reply'           => $reply,
                'conversation_id' => $conversation->id,
                'state'           => AIStateMachine::IDLE,
            ]);
        }

        // 4. Intent Classification & Query Rewriting (Model 1 : Router)
        // Returns: {intent: RAG_DOCS|DB_SEARCH|CHITCHAT, standalone_query, search_entity}
        $activeProvider = $this->resolveHealthyProvider();
        if ($activeProvider !== null) {
            $this->queryRewriter = new QueryRewriterService($activeProvider);
        }
        $history         = $this->contextManager->getFormattedMessageHistory(6);
        $classified      = $this->queryRewriter->classify($userMsg, $history);
        $intent          = $classified['intent'];
        $effectiveMsg    = $classified['standalone_query'];
        $searchEntity    = $classified['search_entity'] ?? null;

        // Short-circuit: CHITCHAT : respond naturally, no tool/LLM needed
        if ($intent === QueryRewriterService::INTENT_CHITCHAT) {
            $chitchatReplies = [
                'Sama-sama! Ada yang bisa saya bantu lagi?',
                'Tentu! Silakan tanyakan data owner, booking, stok obat, atau laporan omzet.',
                'Baik! Ada pertanyaan lain terkait operasional klinik?',
            ];
            $reply = $chitchatReplies[array_rand($chitchatReplies)];
            $this->contextManager->addMessage('assistant', $reply);
            return $this->wrapResponseWithSkill([
                'status'          => 'success',
                'reply'           => $reply,
                'conversation_id' => $conversation->id,
                'state'           => AIStateMachine::IDLE,
            ]);
        }

        $effectiveLower = trim(preg_replace('/[^\w\s]/u', '', strtolower($effectiveMsg)));
        $activeSkill    = $this->contextManager->getActiveSkill();

        // 5a. RAG_DOCS intent : skip DB tool resolver, route directly to LLM
        //     (system_manual content injected via LLM fallback below; no tool needed)
        $resolvedToolIntent = null;
        if ($intent === QueryRewriterService::INTENT_DB || $activeSkill !== null) {
            // 5b. DB_SEARCH intent : Local NLU Tool Resolver
            // Order matters: specialist mode fast paths first, most specific first, broad keywords last.
            $resolvedToolIntent = $this->resolveLocalToolIntent($effectiveMsg, $effectiveLower, $activeSkill);
        }

        if ($resolvedToolIntent !== null) {
            // Conversational clarification before executing broad/underspecified database tools
            if (!empty($resolvedToolIntent['clarification_required'])) {
                $this->contextManager->updateState(AIStateMachine::COLLECTING_DATA);
                $this->contextManager->mergeContextData([
                    'pending_clarification' => [
                        'type'       => $resolvedToolIntent['type'],
                        'created_at' => now()->toIso8601String(),
                    ],
                ]);

                $this->contextManager->addMessage('assistant', $resolvedToolIntent['reply']);
                return $this->wrapResponseWithSkill([
                    'status'          => 'success',
                    'reply'           => $resolvedToolIntent['reply'],
                    'suggestions'     => $resolvedToolIntent['suggestions'] ?? [],
                    'action_url'      => $resolvedToolIntent['action_url'] ?? null,
                    'action_label'    => $resolvedToolIntent['action_label'] ?? null,
                    'conversation_id' => $conversation->id,
                    'state'           => AIStateMachine::COLLECTING_DATA,
                ]);
            }

            $toolObj = $this->toolRegistry->getTool($resolvedToolIntent['tool_name']);
            if ($toolObj) {
                if ($toolObj->requiresConfirmation()) {
                    $this->contextManager->updateState(AIStateMachine::WAITING_CONFIRMATION);
                    $this->contextManager->mergeContextData([
                        'pending_tool' => [
                            'name'       => $toolObj->getName(),
                            'parameters' => $resolvedToolIntent['parameters'],
                        ],
                    ]);

                    $params = $resolvedToolIntent['parameters'];
                    $reply  = "<strong>Konfirmasi Diperlukan</strong>:<br>"
                            . "Apakah Anda yakin ingin membuat janji temu baru dengan rincian berikut?<br>"
                            . "- <strong>Owner</strong>: " . ($params['owner'] ?? '-') . "<br>"
                            . "- <strong>Pasien Hewan</strong>: " . ($params['pet'] ?? '-') . "<br>"
                            . "- <strong>Tanggal</strong>: " . ($params['date'] ?? date('Y-m-d')) . "<br><br>"
                            . "Ketik <strong>'Ya'</strong> untuk menyetujui atau <strong>'Batal'</strong> untuk membatalkan.";

                    $this->contextManager->addMessage('assistant', $reply);
                    return $this->wrapResponseWithSkill([
                        'status'          => 'success',
                        'reply'           => $reply,
                        'conversation_id' => $conversation->id,
                        'state'           => AIStateMachine::WAITING_CONFIRMATION,
                    ]);
                }

                // Read-only tool: execute directly
                $this->contextManager->updateState(AIStateMachine::EXECUTING);
                $execResult    = $this->toolExecutor->execute($toolObj, $resolvedToolIntent['parameters'], $user, $conversation->id);
                $this->contextManager->updateState(AIStateMachine::COMPLETED);

                $formattedReply = $this->formatToolResultToNaturalResponse($toolObj->getName(), $execResult);
                $this->contextManager->addMessage('assistant', $formattedReply['reply']);

                // Tool Result Context: store brief summary for cross-turn reference
                if ($execResult['status'] === 'success') {
                    $plainReply = strip_tags($formattedReply['reply']);
                    $this->contextManager->storeToolResult(
                        $toolObj->getName(),
                        mb_substr($plainReply, 0, 300)
                    );

                    // Period memory: store omzet periods for context-aware compare
                    $tName = $toolObj->getName();
                    if ($tName === 'get_omzet_report') {
                        $storedPeriod = $resolvedToolIntent['parameters']['period'] ?? null;
                        if ($storedPeriod) {
                            $this->contextManager->storeOmzetPeriod($storedPeriod);
                        }
                    } elseif ($tName === 'compare_omzet') {
                        $pA = $resolvedToolIntent['parameters']['period_a'] ?? null;
                        $pB = $resolvedToolIntent['parameters']['period_b'] ?? null;
                        if ($pA) $this->contextManager->storeOmzetPeriod($pA);
                        if ($pB) $this->contextManager->storeOmzetPeriod($pB);
                    }
                }

                return $this->wrapResponseWithSkill(array_merge([
                    'status'          => 'success',
                    'conversation_id' => $conversation->id,
                    'state'           => AIStateMachine::COMPLETED,
                ], $formattedReply));
            }
        }

        // 6. LLM Fallback with provider chain (primary -> fallbacks)
        // $activeProvider already resolved above for query rewriter : reuse it
        if ($activeProvider !== null) {
            try {
                // Summarization (Metode 3): auto-compress when history grows long
                $this->maybeSummarizeHistory($activeProvider);

                $history      = $this->contextManager->getFormattedMessageHistory(8);

                // Inject rewritten query as last user message so LLM gets standalone context
                if (!empty($history)) {
                    for ($i = count($history) - 1; $i >= 0; $i--) {
                        if (($history[$i]['role'] ?? '') === 'user') {
                            $history[$i]['content'] = $effectiveMsg;
                            break;
                        }
                    }
                }

                $systemPrompt = AIKnowledgeBase::systemPrompt($activeSkill);

                // History without last user turn (embedded in runtimeMessage)
                $historyWithoutLast = $history;
                array_pop($historyWithoutLast);

                // Determine context blocks by intent
                $liveDataContext = '';
                $manualContent   = '';

                if ($intent === QueryRewriterService::INTENT_RAG || $activeSkill !== null) {
                    // RAG_DOCS: inject matching SOP docs from internal knowledge base
                    $manualContent = AIKnowledgeDocs::getRelevantDocs($effectiveMsg, 3, $activeSkill);
                    // Append entity context as supplemental session data if present
                    $entityCtx = $this->contextManager->getEntityContext();
                    if ($entityCtx) {
                        $manualContent .= "\n\n" . $entityCtx;
                    }
                } else {
                    // DB_SEARCH (or fallback): live_data_result gets tool results + entity memory
                    $liveDataParts = [];
                    $entityCtx     = $this->contextManager->getEntityContext();
                    if ($entityCtx) $liveDataParts[] = $entityCtx;
                    $lastResults = $this->contextManager->getLastResultsContext();
                    if ($lastResults) $liveDataParts[] = $lastResults;
                    $liveDataContext = implode("\n\n", $liveDataParts);
                }

                // Build XML-structured user message (Model 3 runtime input schema)
                $runtimeUserMessage = AIKnowledgeBase::buildRuntimeMessage(
                    $effectiveMsg,
                    $historyWithoutLast,
                    $liveDataContext,
                    $manualContent
                );

                $messages    = [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => $runtimeUserMessage],
                ];
                $llmResponse = $activeProvider->chatCompletion($messages, $this->toolRegistry->exportFunctionSchemas());

                if (isset($llmResponse['choices'][0]['message']['content'])) {
                    $reply = nl2br(e($llmResponse['choices'][0]['message']['content']));
                    $this->contextManager->addMessage('assistant', $reply);
                    return $this->wrapResponseWithSkill([
                        'status'          => 'success',
                        'reply'           => $reply,
                        'conversation_id' => $conversation->id,
                        'state'           => AIStateMachine::COMPLETED,
                    ]);
                }
            } catch (Exception $e) {
                Log::warning('LLM Orchestrator Warning: ' . $e->getMessage());
            }
        }

        // 5. Static fallback : Layer 1: detect ambiguous input before generic reply
        $isAmbiguous = strlen(trim($lowerMsg)) >= 3
            && strlen(trim($lowerMsg)) <= 60
            && !str_contains($lowerMsg, 'owner')
            && !str_contains($lowerMsg, 'booking')
            && !str_contains($lowerMsg, 'obat')
            && !str_contains($lowerMsg, 'stok')
            && !str_contains($lowerMsg, 'omzet')
            && !str_contains($lowerMsg, 'layanan')
            && !str_contains($lowerMsg, 'karyawan')
            && !str_contains($lowerMsg, 'laporan');

        if ($activeSkill) {
            $skillMeta = AISkillManager::getSkill($activeSkill);
            $title = $skillMeta['title'] ?? 'Spesialis';
            $fallbackText = "Halo! Anda sedang berada di mode <strong>{$title}</strong>.<br>"
                          . "Silakan ajukan pertanyaan terkait modul ini atau gunakan <strong>/general</strong> untuk kembali ke mode asisten umum.";
        } elseif ($isAmbiguous) {
            $fallbackText = "Boleh saya bantu lebih lanjut? 😊<br>"
                          . "Maksud Anda terkait <strong>data owner/hewan</strong>, <strong>booking/antrean</strong>, <strong>stok obat</strong>, atau <strong>laporan omzet</strong>?";
        } else {
            $fallbackText = "Halo! Saya adalah <strong>VetClinic AI Assistant</strong> Assyavet.<br>"
                          . "Saya dapat membantu data <strong>Owner, Booking, Rawat Inap, Stok Obat, Tarif Layanan, &amp; Omzet Klinik</strong>.<br>"
                          . "Ketik pertanyaan Anda, misalnya: <em>\"antrean hari ini\"</em> atau <em>\"cek stok amoxicillin\"</em>.";
        }

        $this->contextManager->addMessage('assistant', $fallbackText);

        return $this->wrapResponseWithSkill([
            'status'          => 'success',
            'reply'           => $fallbackText,
            'conversation_id' => $conversation->id,
            'state'           => AIStateMachine::IDLE,
        ]);
    }

    /**
     * Walk provider chain and return first healthy provider.
     */
    protected function resolveHealthyProvider(): ?AIProviderInterface
    {
        if ($this->provider->checkHealth()) {
            return $this->provider;
        }

        foreach ($this->fallbackProviders as $fallback) {
            try {
                if ($fallback->checkHealth()) {
                    return $fallback;
                }
            } catch (Exception $e) {
                Log::warning('Fallback provider health check failed: ' . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Summarization Memory (Metode 3 + Sliding Window Metode 2):
     * When message count exceeds threshold, ask LLM to summarize older messages,
     * store summary in context_data['summary'], inject into next getFormattedMessageHistory().
     */
    protected function maybeSummarizeHistory(AIProviderInterface $provider): void
    {
        $threshold = config('ai.summarize_threshold', 20);
        $count = $this->contextManager->getMessageCount();

        if ($count < $threshold) return;

        $convId = $this->contextManager->getConversationId();
        if (!$convId) return;

        $oldMessages = \App\Models\AIMessage::where('conversation_id', $convId)
            ->oldest('id')
            ->take($count - 8)
            ->get();

        if ($oldMessages->isEmpty()) return;

        $transcript = $oldMessages->map(fn($m) => ucfirst($m->role) . ': ' . strip_tags($m->content))
                                   ->implode("\n");

        try {
            $summaryResponse = $provider->chatCompletion([
                ['role' => 'system', 'content' => 'Kamu adalah asisten ringkas. Buat ringkasan singkat (maks 200 kata, Bahasa Indonesia) dari percakapan berikut. Fokus pada: topik yang dibahas, data yang ditemukan, tindakan yang dilakukan.'],
                ['role' => 'user',   'content' => $transcript],
            ]);

            $summaryText = $summaryResponse['choices'][0]['message']['content'] ?? null;
            if ($summaryText) {
                $this->contextManager->storeSummary(trim($summaryText));
                // Delete summarized messages to keep DB lean
                \App\Models\AIMessage::where('conversation_id', $convId)
                    ->oldest('id')
                    ->take($count - 8)
                    ->delete();
                Log::info("AI Summarization: compressed {$count} messages for conversation {$convId}.");
            }
        } catch (Exception $e) {
            Log::warning('AI Summarization failed: ' . $e->getMessage());
        }
    }

    protected function normalizeTyposAndSynonyms(string $msg): string
    {
        $typoMap = [
            '/\b(catatn|catatna|cttn|catat)\b/i'                         => 'catatan',
            '/\b(pengambiln|pengabilan)\b/i'                              => 'pengambilan',
            '/\b(boking|bokingan|bokink|bokingg|boocking)\b/i'            => 'booking',
            '/\b(omset|omztt|omzeet|omsett)\b/i'                          => 'omzet',
            '/\b(invtori|invntori|invetori|inventoer)\b/i'                => 'inventori',
            '/\b(layan|laynan|layann|layanan2)\b/i'                       => 'layanan',
            '/\b(stk|stoknya|stokk|stok2)\b/i'                           => 'stok',
            '/\b(pmlik|pemlik|pmlk|ownr)\b/i'                            => 'pemilik',
            '/\b(doktr|doktrr|drh|dr)\b/i'                               => 'dokter',
            '/\b(tmb|tamb|tmbh|tambh)\b/i'                               => 'tambah',
            '/\b(jnji|jni temu|jnji temu)\b/i'                           => 'janji temu',
        ];

        return preg_replace(array_keys($typoMap), array_values($typoMap), $msg);
    }

    protected function isConfirmationAffirmative(string $lowerMsg): bool
    {
        return in_array($lowerMsg, ['ya', 'y', 'iya', 'setuju', 'ok', 'oke', 'lanjut', 'buat', 'proses', 'yes', 'sure', 'yap', 'yep']);
    }

    protected function isConfirmationNegative(string $lowerMsg): bool
    {
        return in_array($lowerMsg, ['batal', 'batalkan', 'tidak', 'tdk', 'ga', 'gak', 'enggak', 'cancel', 'stop', 'no', 'nope']);
    }

    /**
     * Local NLU intent resolver.
     *
     * Priority order (most specific first):
     *  1. Mutating: create_appointment
     *  2. Report: get_omzet_report
     *  3. Temporary invoice: get_temporary_invoice
     *  4. Inventory/pharmacy (before generic owner to avoid "obat" matching owner)
     *  5. Owner search
     *  6. Appointment list (broad : booking/antrean keyword)
     */
    protected function resolveLocalToolIntent(string $rawMsg, string $lowerMsg, ?string $activeSkill = null): ?array
    {
        // 1. Create appointment : most specific, requires "buat/tambah/bikin/daftar" + booking intent
        // Exclude informational queries: "alur", "step", "cara", "bagaimana", "proses", "tahap"
        $isInfoQuery    = (str_contains($lowerMsg, 'alur') || str_contains($lowerMsg, 'step')
            || str_contains($lowerMsg, 'cara') || str_contains($lowerMsg, 'bagaimana')
            || str_contains($lowerMsg, 'proses') || str_contains($lowerMsg, 'tahap')
            || str_contains($lowerMsg, 'queue') || str_contains($lowerMsg, 'stage'));
        $isCreateIntent = (str_contains($lowerMsg, 'buat') || str_contains($lowerMsg, 'tambah')
            || str_contains($lowerMsg, 'bikin') || str_contains($lowerMsg, 'daftar')
            || str_contains($lowerMsg, 'jadwal'));
        $isBookingTarget = (str_contains($lowerMsg, 'booking') || str_contains($lowerMsg, 'appointment')
            || str_contains($lowerMsg, 'janji temu') || str_contains($lowerMsg, 'janji'));

        if ($isCreateIntent && $isBookingTarget && !$isInfoQuery) {
            $owner = '';
            $pet   = '';

            if (preg_match('/(?:owner|pemilik|untuk)\s+([a-zA-Z\s]+?)(?:\s+(?:hewan|pet|kucing|anjing|burung|dengan|tanggal|$))/i', $rawMsg, $m)) {
                $owner = trim($m[1]);
            }
            if (preg_match('/(?:hewan|pet|pasien|kucing|anjing|burung)\s+([a-zA-Z0-9\s]+?)(?:\s+(?:owner|pemilik|tanggal|pada|$))/i', $rawMsg, $m)) {
                $pet = trim($m[1]);
            }

            $date = date('Y-m-d');
            if (preg_match('/(\d{4}-\d{2}-\d{2})/', $rawMsg, $m)) {
                $date = $m[1];
            } elseif (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/', $rawMsg, $m)) {
                $date = "{$m[3]}-{$m[2]}-{$m[1]}";
            }

            return [
                'tool_name'  => 'create_appointment',
                'parameters' => ['owner' => $owner, 'pet' => $pet, 'date' => $date],
            ];
        }

        // Domain-specific fast path when a modular specialist skill is active
        if (!$isInfoQuery && !empty($activeSkill)) {
            $specialistIntent = $this->resolveSpecialistIntent($rawMsg, $lowerMsg, $activeSkill);
            if ($specialistIntent !== null) {
                return $specialistIntent;
            }
        }

        // 2a. Compare omzet : MUST come before get_omzet_report (also matches 'omzet')
        $isCompareIntent = str_contains($lowerMsg, 'bandingkan') || str_contains($lowerMsg, 'perbandingan')
            || str_contains($lowerMsg, 'dibanding') || str_contains($lowerMsg, ' vs ');
        $isOmzetKeyword  = str_contains($lowerMsg, 'omzet') || str_contains($lowerMsg, 'pendapatan')
            || str_contains($lowerMsg, 'pemasukan');
        // "bandingkan kedua bulan itu/tadi" : compare without explicit omzet keyword
        $isCompareBulan  = $isCompareIntent && str_contains($lowerMsg, 'bulan');

        if ($isCompareIntent && ($isOmzetKeyword || $isCompareBulan)) {
            $months  = $this->extractMonthsFromMessage($rawMsg);

            // Context-aware fallback: pull from session history if no months in message
            if (count($months) < 2) {
                $recent  = $this->contextManager->getRecentOmzetPeriods(2);
                // If user said "kedua bulan itu/tadi" resolve from history
                $wantsHistory = str_contains($lowerMsg, 'kedua') || str_contains($lowerMsg, 'itu')
                    || str_contains($lowerMsg, 'tadi') || str_contains($lowerMsg, 'tersebut');
                if ($wantsHistory && count($recent) >= 2) {
                    $months = $recent;
                }
            }

            $periodA = $months[0] ?? 'last_month';
            $periodB = $months[1] ?? 'this_month';
            return [
                'tool_name'  => 'compare_omzet',
                'parameters' => ['period_a' => $periodA, 'period_b' => $periodB],
            ];
        }

        // 2b. Omzet / revenue report (single period)
        if ($isOmzetKeyword || str_contains($lowerMsg, 'keuangan')) {
            if (str_contains($lowerMsg, 'hari ini')) {
                $period = 'today';
            } elseif (str_contains($lowerMsg, 'tahun lalu')) {
                $lastYear = (int) date('Y') - 1;
                $period   = (string) $lastYear;
            } elseif (str_contains($lowerMsg, 'tahun ini') || preg_match('/\btahun\b/', $lowerMsg)) {
                $period = 'year';
            } elseif (str_contains($lowerMsg, 'bulan lalu')) {
                $period = 'last_month';
            } else {
                // Try to detect named month ("omzet bulan maret", "omzet juli")
                $months = $this->extractMonthsFromMessage($rawMsg);
                $period = $months[0] ?? 'month'; // YYYY-MM or 'month'
            }
            return [
                'tool_name'  => 'get_omzet_report',
                'parameters' => ['period' => $period],
            ];
        }

        // 3. Estimasi / temporary invoice
        if (str_contains($lowerMsg, 'estimasi') || str_contains($lowerMsg, 'nota sementara')
            || str_contains($lowerMsg, 'perkiraan biaya')) {
            return ['tool_name' => 'get_temporary_invoice', 'parameters' => []];
        }

        // 4. Staff / karyawan / dokter
        if (str_contains($lowerMsg, 'karyawan') || str_contains($lowerMsg, 'dokter')
            || str_contains($lowerMsg, 'staf') || str_contains($lowerMsg, 'staff')
            || str_contains($lowerMsg, 'berjaga') || str_contains($lowerMsg, 'petugas')
            || str_contains($lowerMsg, 'siapa yang bertugas') || str_contains($lowerMsg, 'daftar pegawai')) {
            $nameClean = trim(str_replace(
                ['karyawan', 'dokter', 'staf', 'staff', 'berjaga', 'petugas', 'siapa',
                 'bertugas', 'daftar', 'cari', 'lihat', 'hari', 'ini', 'pegawai'],
                '',
                $lowerMsg
            ));
            $date = date('Y-m-d');
            if (preg_match('/(\d{4}-\d{2}-\d{2})/', $rawMsg, $m)) {
                $date = $m[1];
            } elseif (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/', $rawMsg, $m)) {
                $date = "{$m[3]}-{$m[2]}-{$m[1]}";
            }
            return [
                'tool_name'  => 'get_staff',
                'parameters' => ['query' => trim($nameClean), 'date' => $date],
            ];
        }

        // 5. Layanan / tarif / harga layanan
        if (str_contains($lowerMsg, 'layanan') || str_contains($lowerMsg, 'tarif')
            || str_contains($lowerMsg, 'harga layanan') || str_contains($lowerMsg, 'jasa dokter')
            || str_contains($lowerMsg, 'biaya layanan') || str_contains($lowerMsg, 'daftar layanan')) {
            $clean = trim(str_replace(
                ['layanan', 'tarif', 'harga', 'biaya', 'daftar', 'cari', 'lihat', 'jasa'],
                '',
                $lowerMsg
            ));
            return [
                'tool_name'  => 'search_layanan',
                'parameters' => ['query' => trim($clean)],
            ];
        }

        // 6. Transaksi / catatan pengambilan obat
        if (str_contains($lowerMsg, 'catatan pengambilan') || str_contains($lowerMsg, 'pengambilan obat')
            || str_contains($lowerMsg, 'log transaksi') || str_contains($lowerMsg, 'riwayat pengambilan')
            || (str_contains($lowerMsg, 'catatan') && str_contains($lowerMsg, 'obat'))
            || str_contains($lowerMsg, 'obat diambil') || str_contains($lowerMsg, 'transaksi obat')) {
            $bookingCode = '';
            if (preg_match('/\b(BOOK[A-Z0-9]{7,})\b/i', $rawMsg, $m)) {
                $bookingCode = strtoupper($m[1]);
            }
            $query = trim(str_replace(
                ['catatan', 'pengambilan', 'obat', 'log', 'transaksi', 'riwayat', 'ambil', 'diambil'],
                '', $lowerMsg
            ));
            $period = 'today';
            if (str_contains($lowerMsg, 'bulan ini'))       $period = 'month';
            elseif (str_contains($lowerMsg, 'minggu ini'))  $period = 'week';
            return [
                'tool_name'  => 'get_transaksi_log',
                'parameters' => [
                    'booking_code' => $bookingCode,
                    'query'        => trim($query),
                    'period'       => $period,
                ],
            ];
        }

        // 6b. Booking detail : spesifik kode BOOK atau "detail booking"
        $isDetailIntent = str_contains($lowerMsg, 'detail booking')
            || str_contains($lowerMsg, 'info booking')
            || str_contains($lowerMsg, 'lihat booking')
            || preg_match('/\bbook[a-z0-9]{5,}/i', $rawMsg);
        if ($isDetailIntent && !$isInfoQuery) {
            $kode = '';
            if (preg_match('/\b(BOOK[A-Z0-9]{5,})\b/i', $rawMsg, $m)) {
                $kode = strtoupper($m[1]);
            }
            $petClean = '';
            if (preg_match('/(?:hewan|kucing|anjing|pasien)\s+([a-zA-Z0-9]+)/i', $rawMsg, $m)) {
                $petClean = trim($m[1]);
            }
            $ownerClean = '';
            if (preg_match('/(?:owner|pemilik)\s+([a-zA-Z\s]{2,30}?)(?:\s+|$)/i', $rawMsg, $m)) {
                $ownerClean = trim($m[1]);
            }

            // Clarify if no specific identifier provided
            if (empty($kode) && empty($petClean) && empty($ownerClean)) {
                return [
                    'clarification_required' => true,
                    'type'                   => 'booking_detail',
                    'reply'                  => "Untuk melihat <strong>detail booking</strong> (tanda vital, SOAP EMR, tindakan layanan, dan rincian biaya), silakan sebutkan identitas booking yang ingin dilihat:<br><br>"
                                              . "• 🏷️ <strong>Kode Booking:</strong> (contoh: <em>BOOK9Z1FXYI</em>)<br>"
                                              . "• 👤 <strong>Nama Pemilik atau Pasien:</strong> (contoh: <em>\"detail booking Doni\"</em> atau <em>\"detail booking Cleo\"</em>)<br><br>"
                                              . "Atau ketik <em>\"antrean hari ini\"</em> untuk melihat daftar pasien aktif hari ini.",
                    'suggestions'            => ['Antrean Hari Ini', 'BOOK9Z1FXYI', 'Buka Manajemen Booking'],
                    'action_url'             => route('booking-uncompleted'),
                    'action_label'           => 'Buka Manajemen Booking',
                ];
            }

            return [
                'tool_name'  => 'get_booking_detail',
                'parameters' => ['kode_booking' => $kode, 'owner' => $ownerClean, 'pet' => $petClean],
            ];
        }

        // 6c. EMR / rekam medis / diagnosa / SOAP
        $isEmrKeyword = str_contains($lowerMsg, 'rekam medis') || str_contains($lowerMsg, 'emr')
            || str_contains($lowerMsg, 'soap') || str_contains($lowerMsg, 'diagnosa')
            || str_contains($lowerMsg, 'subjective') || str_contains($lowerMsg, 'objective')
            || str_contains($lowerMsg, 'assessment') || str_contains($lowerMsg, 'plan terapi')
            || (str_contains($lowerMsg, 'diagnos') && !str_contains($lowerMsg, 'diagnosa_penunjang'))
            || str_contains($lowerMsg, 'hasil periksa') || str_contains($lowerMsg, 'tanda vital')
            || (str_contains($lowerMsg, 'keluhan') && (
                str_contains($lowerMsg, 'kucing') || str_contains($lowerMsg, 'anjing')
                || str_contains($lowerMsg, 'hewan') || str_contains($lowerMsg, 'pasien')
            ));
        if ($isEmrKeyword) {
            $kode = '';
            if (preg_match('/\b(BOOK[A-Z0-9]{5,})\b/i', $rawMsg, $m)) {
                $kode = strtoupper($m[1]);
            }
            $petClean = '';
            if (preg_match('/(?:kucing|anjing|burung|kelinci|hewan|pasien)\s+([a-zA-Z0-9]+)/i', $rawMsg, $m)) {
                $petClean = trim($m[1]);
            }
            $ownerClean = '';
            if (preg_match('/(?:owner|pemilik)\s+([a-zA-Z\s]{2,30}?)(?:\s+|$)/i', $rawMsg, $m)) {
                $ownerClean = trim($m[1]);
            }

            // Clarify if no patient or booking code specified
            if (empty($kode) && empty($petClean) && empty($ownerClean)) {
                return [
                    'clarification_required' => true,
                    'type'                   => 'emr',
                    'reply'                  => "Untuk memeriksa <strong>Rekam Medis (SOAP, Tanda Vital, &amp; Riwayat Terapi)</strong>, pasien mana yang ingin diperiksa?<br><br>"
                                              . "• 🐾 <strong>Nama Hewan:</strong> (contoh: <em>\"rekam medis Koci\"</em> atau <em>\"diagnosa Cleo\"</em>)<br>"
                                              . "• 🏷️ <strong>Kode Booking:</strong> (contoh: <em>\"EMR BOOK9Z1FXYI\"</em>)<br><br>"
                                              . "Silakan ketik nama pasien hewan atau kode booking.",
                    'suggestions'            => ['Antrean Hari Ini', 'Rekam Medis Koci', 'Buka Manajemen Booking'],
                    'action_url'             => route('booking-uncompleted'),
                    'action_label'           => 'Buka Manajemen Booking',
                ];
            }

            $limit = str_contains($lowerMsg, 'riwayat') || str_contains($lowerMsg, 'semua') ? 3 : 1;
            return [
                'tool_name'  => 'get_emr',
                'parameters' => [
                    'kode_booking' => $kode,
                    'pet'          => $petClean,
                    'owner'        => $ownerClean,
                    'limit'        => $limit,
                ],
            ];
        }

        // 7. Compare omzet antar bulan
        if ((str_contains($lowerMsg, 'bandingkan') || str_contains($lowerMsg, 'perbandingan')
            || str_contains($lowerMsg, 'vs') || str_contains($lowerMsg, 'dibanding'))
            && (str_contains($lowerMsg, 'omzet') || str_contains($lowerMsg, 'pendapatan')
                || str_contains($lowerMsg, 'pemasukan'))) {
            // Try to parse two YYYY-MM periods from the message
            preg_match_all('/(\d{4}-\d{2}|\d{1,2}[-\/]\d{4})/', $rawMsg, $matches);
            $periods  = $matches[0] ?? [];
            $periodA  = $periods[0] ?? 'last_month';
            $periodB  = $periods[1] ?? 'this_month';
            // Normalise DD/MM/YYYY-style fragments to YYYY-MM
            $periodA  = $this->normalizeMonthPeriod($periodA);
            $periodB  = $this->normalizeMonthPeriod($periodB);
            return [
                'tool_name'  => 'compare_omzet',
                'parameters' => ['period_a' => $periodA, 'period_b' => $periodB],
            ];
        }

        // 8. Inventory / pharmacy / medicine stock
        if (str_contains($lowerMsg, 'stok') || str_contains($lowerMsg, 'obat')
            || str_contains($lowerMsg, 'sisa') || str_contains($lowerMsg, 'inventori')
            || str_contains($lowerMsg, 'apotek')) {
            $clean = trim(preg_replace(
                '/\b(cari|stok|stoknya|obat|sisa|jumlah|ada|berapa|banyak|masih|cek|apakah|tolong|inventori|apotek|di|farmasi|barang|medis)\b/u',
                '',
                $lowerMsg
            ));
            $isLowStock = str_contains($lowerMsg, 'menipis') || str_contains($lowerMsg, 'habis') || str_contains($lowerMsg, 'sedikit');

            if ($isLowStock) {
                return [
                    'tool_name'  => 'search_inventory',
                    'parameters' => ['query' => 'menipis'],
                ];
            }

            if (empty($clean) || strlen($clean) < 2) {
                return [
                    'clarification_required' => true,
                    'type'                   => 'inventory_search',
                    'reply'                  => "Obat atau barang medis apa yang ingin Anda periksa ketersediaannya di inventori apotek?<br><br>"
                                              . "• 💊 <strong>Ketik nama obat/barang:</strong> (contoh: <em>\"stok amoxicillin\"</em> atau <em>\"stok catgut\"</em>)<br>"
                                              . "• ⚠️ <strong>Stok Menipis:</strong> ketik <em>\"stok menipis\"</em> untuk melihat obat yang stoknya hampir habis.<br><br>"
                                              . "Silakan sebutkan nama obat yang ingin dicek.",
                    'suggestions'            => ['Stok Menipis', 'Cari Amoxicillin', 'Buka Stok Inventori'],
                    'action_url'             => route('inventori'),
                    'action_label'           => 'Buka Stok Inventori',
                ];
            }

            return [
                'tool_name'  => 'search_inventory',
                'parameters' => ['query' => $clean],
            ];
        }

        // 9. Owner / pet owner search
        $isOwnerKeyword = str_contains($lowerMsg, 'pemilik')
            || str_contains($lowerMsg, 'owner')
            || str_contains($lowerMsg, 'customer')
            || str_contains($lowerMsg, 'pelanggan')
            || str_contains($lowerMsg, 'klien')
            || str_contains($lowerMsg, 'milik siapa')
            || str_contains($lowerMsg, 'cari pemilik')
            || str_contains($lowerMsg, 'data owner')
            || str_contains($lowerMsg, 'data customer');

        if ($isOwnerKeyword) {
            $clean = trim(preg_replace(
                '/\b(siapa|pemilik|owner|customer|pelanggan|klien|milik|kucing|anjing|hewan|pasien|punya|dari|cari|data|dan|nya|ada|tolong|minta|tampilkan|lihat|info|semua|daftar|terbaru|tentang|bisa|mohon)\b/u',
                '',
                $lowerMsg
            ));

            $isListRequest = str_contains($lowerMsg, 'terbaru')
                || str_contains($lowerMsg, 'daftar')
                || str_contains($lowerMsg, 'semua')
                || str_contains($lowerMsg, 'list');

            if ($isListRequest) {
                return [
                    'tool_name'  => 'search_owner',
                    'parameters' => ['query' => ''],
                ];
            }

            if (empty($clean) || strlen($clean) < 2) {
                return [
                    'clarification_required' => true,
                    'type'                   => 'owner_search',
                    'reply'                  => "Tentu! Saya siap membantu mencari <strong>data customer (owner) dan hewan peliharaannya</strong>.<br><br>"
                                              . "Agar hasil pencariannya tepat dan relevan, informasi apa yang ingin Anda cari?<br>"
                                              . "• 👤 <strong>Cari by Nama / No. HP:</strong> Sebutkan nama pemilik atau nomor telepon (contoh: <em>\"Doni\"</em> atau <em>\"0813...\"</em>)<br>"
                                              . "• 🐾 <strong>Cari by Pasien Hewan:</strong> Sebutkan nama hewan (contoh: <em>\"kucing Koci\"</em>)<br>"
                                              . "• 📋 <strong>Lihat Data Terbaru:</strong> Ketik <em>\"customer terbaru\"</em> untuk melihat daftar pelanggan terdaftar.<br><br>"
                                              . "Silakan ketik nama atau nomor telepon yang ingin dicari.",
                    'suggestions'            => ['Customer Terbaru', 'Cari Doni', 'Kucing Koci', 'Buka Menu Owner'],
                    'action_url'             => route('owner'),
                    'action_label'           => 'Buka Master Data Owner',
                ];
            }

            return [
                'tool_name'  => 'search_owner',
                'parameters' => ['query' => $clean],
            ];
        }

        // 10. Appointment list : broad keyword, check for booking/antrean only after all above
        if (str_contains($lowerMsg, 'booking') || str_contains($lowerMsg, 'antrean')
            || str_contains($lowerMsg, 'antrian') || str_contains($lowerMsg, 'jadwal pasien')
            || str_contains($lowerMsg, 'daftar pasien')) {
            $period = 'today';
            if (str_contains($lowerMsg, 'bulan lalu'))      $period = 'last_month';
            elseif (str_contains($lowerMsg, 'bulan ini'))   $period = 'this_month';
            elseif (str_contains($lowerMsg, 'kemarin'))     $period = 'yesterday';
            elseif (str_contains($lowerMsg, 'besok'))       $period = 'tomorrow';
            elseif (str_contains($lowerMsg, 'mendatang') || str_contains($lowerMsg, 'depan')) $period = 'upcoming';
            elseif (str_contains($lowerMsg, 'semua') || str_contains($lowerMsg, 'all'))       $period = 'all';

            // Extract optional search query
            $query = '';
            if (preg_match('/(?:pasien|owner|dokter)\s+([a-zA-Z0-9\s]+)/i', $rawMsg, $m)) {
                $query = trim($m[1]);
            }

            return [
                'tool_name'  => 'get_appointments',
                'parameters' => ['period' => $period, 'query' => $query],
            ];
        }

        return null;
    }

    /**
     * Normalise a user-typed month reference to YYYY-MM for CompareOmzetTool.
     * Accepts: "2026-07", "7/2026", "7-2026", "bulan_ini", "last_month",
     *          Indonesian month names: "januari", "februari", "maret", etc.
     */
    protected function normalizeMonthPeriod(string $raw): string
    {
        $raw = trim(strtolower($raw));
        if (in_array($raw, ['bulan_ini', 'this_month', 'bulan ini'])) return 'this_month';
        if (in_array($raw, ['bulan_lalu', 'last_month', 'bulan lalu'])) return 'last_month';

        // Indonesian month name → YYYY-MM
        $monthMap = [
            'januari'=>1,'februari'=>2,'maret'=>3,'april'=>4,
            'mei'=>5,'juni'=>6,'juli'=>7,'agustus'=>8,
            'september'=>9,'oktober'=>10,'november'=>11,'desember'=>12,
            'jan'=>1,'feb'=>2,'mar'=>3,'apr'=>4,
            'jun'=>6,'jul'=>7,'agu'=>8,'agt'=>8,
            'sep'=>9,'okt'=>10,'nov'=>11,'des'=>12,
        ];
        if (isset($monthMap[$raw])) {
            $year = now()->year;
            $num  = $monthMap[$raw];
            if ($num > now()->month) $year--; // named month in future → assume last year
            return $year . '-' . str_pad($num, 2, '0', STR_PAD_LEFT);
        }

        // Already YYYY-MM
        if (preg_match('/^(\d{4})-(\d{2})$/', $raw)) return $raw;
        // M/YYYY or M-YYYY
        if (preg_match('/^(\d{1,2})[\/\-](\d{4})$/', $raw, $m)) {
            return $m[2] . '-' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
        }
        return $raw;
    }

    /**
     * Scan free-text for up to 2 month references (named or numeric), return normalized YYYY-MM list.
     */
    protected function extractMonthsFromMessage(string $rawMsg): array
    {
        $monthNames = [
            'januari','februari','maret','april','mei','juni',
            'juli','agustus','september','oktober','november','desember',
            'jan','feb','mar','apr','jun','jul','agu','agt','sep','okt','nov','des',
        ];
        $lower     = strtolower($rawMsg);
        $positions = [];
        foreach ($monthNames as $name) {
            $pos = strpos($lower, $name);
            if ($pos !== false) {
                $positions[$pos] = $name;
            }
        }
        ksort($positions);

        $found = [];
        foreach ($positions as $name) {
            $n = $this->normalizeMonthPeriod($name);
            if (!in_array($n, $found)) $found[] = $n;
            if (count($found) >= 2) break;
        }

        // Fallback to numeric patterns
        if (count($found) < 2) {
            preg_match_all('/(\d{4}-\d{2}|\d{1,2}[\/\-]\d{4})/', $rawMsg, $matches);
            foreach (($matches[0] ?? []) as $raw) {
                $n = $this->normalizeMonthPeriod($raw);
                if (!in_array($n, $found)) $found[] = $n;
                if (count($found) >= 2) break;
            }
        }

        return $found;
    }

    protected function formatToolResultToNaturalResponse(string $toolName, array $execResult): array
    {
        if ($execResult['status'] !== 'success') {
            return [
                'reply'   => "Gagal memproses data: " . ($execResult['message'] ?? 'Terjadi kesalahan.'),
                'results' => [],
            ];
        }

        $data = $execResult['data'];

        if ($toolName === 'search_inventory') {
            if (!($data['found'] ?? false)) {
                return ['reply' => $data['message'] ?? 'Obat tidak ditemukan.', 'results' => []];
            }
            $items   = $data['items'] ?? [];
            $replies = [];
            $cards   = [];
            foreach ($items as $item) {
                $replies[] = "- <strong>{$item['nama_barang']}</strong> | Stok: <strong>{$item['stok']}</strong> | Harga: <strong>{$item['harga_formatted']}</strong> [{$item['status']}]";
                $cards[]   = [
                    'title'       => $item['nama_barang'],
                    'subtitle'    => "Harga: {$item['harga_formatted']} | Stok: <strong>{$item['stok']}</strong> | Status: {$item['status']}",
                    'badge'       => $item['status'],
                    'badge_class' => $item['stok'] > 10 ? 'bg-success' : 'bg-warning text-dark',
                    'url'         => $item['url'],
                ];
            }
            return [
                'reply'        => "Stok <strong>Inventori &amp; Obat Apotek</strong> yang ditemukan:<br>" . implode("<br>", $replies),
                'results'      => $cards,
                'action_url'   => route('inventori'),
                'action_label' => 'Buka Stok Inventori',
            ];
        }

        if ($toolName === 'search_owner') {
            if (!($data['found'] ?? false)) {
                return ['reply' => $data['message'] ?? 'Owner tidak ditemukan.', 'results' => []];
            }
            $owners  = $data['owners'] ?? [];
            $replies = [];
            $cards   = [];
            foreach ($owners as $o) {
                if (!empty($o['pets'])) {
                    $petList = implode(', ', $o['pets']);
                    $replies[] = "• 👤 <strong>{$o['nama']}</strong> (Telp: " . ($o['telepon'] ?? '-') . ") | Hewan: 🐾 <em>{$petList}</em>";
                } else {
                    $replies[] = "• 👤 <strong>{$o['nama']}</strong> (Telp: " . ($o['telepon'] ?? '-') . ") | Alamat: " . ($o['alamat'] ?? '-');
                }
                $cards[] = [
                    'title'       => "Owner: {$o['nama']}",
                    'subtitle'    => "Telp: " . ($o['telepon'] ?? '-') . " | Alamat: " . ($o['alamat'] ?? '-'),
                    'badge'       => count($o['pets']) . ' Pasien',
                    'badge_class' => 'bg-success',
                    'url'         => $o['url'],
                ];
            }

            $count = count($owners);
            $header = $count === 1
                ? "Ditemukan data pemilik:"
                : "Ditemukan <strong>{$count} data pemilik/customer</strong>:";

            return [
                'reply'        => $header . "<br>" . implode("<br>", $replies),
                'results'      => $cards,
                'action_url'   => route('owner'),
                'action_label' => 'Buka Profil Owner',
            ];
        }

        if ($toolName === 'get_today_appointments' || $toolName === 'get_appointments') {
            $periodTitle = $data['title'] ?? 'Hari Ini';
            if (!($data['found'] ?? false)) {
                return ['reply' => $data['message'] ?? "Belum ada booking untuk periode {$periodTitle}.", 'results' => []];
            }
            $appts   = $data['appointments'] ?? [];
            $replies = [];
            $cards   = [];
            foreach ($appts as $b) {
                $replies[] = "- <strong>[{$b['kode_booking']}]</strong> Pasien: <strong>{$b['pet']}</strong> (Owner: {$b['owner']}) | Dokter: <strong>{$b['dokter']}</strong> | Status: <strong>{$b['status']}</strong>";
                $cards[]   = [
                    'title'       => "[{$b['kode_booking']}] Pasien: {$b['pet']} (Dokter: {$b['dokter']})",
                    'subtitle'    => "Tanggal: {$b['tanggal']} | Status: <strong>{$b['status']}</strong> | Keluhan: " . ($b['keluhan'] ?? '-'),
                    'badge'       => $b['status'],
                    'badge_class' => 'bg-primary',
                    'url'         => $b['url'],
                ];
            }
            return [
                'reply'        => "Data <strong>Booking &amp; Antrean Pasien {$periodTitle}</strong>:<br>" . implode("<br>", $replies),
                'results'      => $cards,
                'action_url'   => route('booking-uncompleted'),
                'action_label' => 'Buka Management Booking',
            ];
        }

        if ($toolName === 'get_omzet_report') {
            return [
                'reply'        => "<strong>Omzet Bersih Klinik {$data['title']}</strong>: <strong>{$data['total_omzet_formatted']}</strong> ({$data['completed_transactions']} transaksi selesai).",
                'results'      => [[
                    'title'       => "Omzet {$data['title']}: {$data['total_omzet_formatted']}",
                    'subtitle'    => "Transaksi Selesai: {$data['completed_transactions']} Kunjungan Pasien",
                    'badge'       => 'Laporan Keuangan',
                    'badge_class' => 'bg-success',
                    'url'         => $data['url'],
                ]],
                'action_url'   => $data['url'],
                'action_label' => 'Buka Laporan Omzet',
            ];
        }

        if ($toolName === 'get_staff') {
            if (!($data['found'] ?? false)) {
                return ['reply' => $data['message'] ?? 'Data karyawan tidak ditemukan.', 'results' => []];
            }
            $staff   = $data['staff'] ?? [];
            $date    = $data['date'] ?? date('Y-m-d');
            $replies = [];
            $cards   = [];
            foreach ($staff as $s) {
                $replies[] = "- <strong>{$s['name']}</strong> ({$s['role']}) : {$s['booking_count']} booking pada {$date}";
                $cards[]   = [
                    'title'       => $s['name'],
                    'subtitle'    => "Role: {$s['role']} | Email: {$s['email']} | Booking hari ini: {$s['booking_count']}",
                    'badge'       => $s['role'],
                    'badge_class' => 'bg-info text-dark',
                    'url'         => route('karyawan'),
                ];
            }
            return [
                'reply'        => "<strong>Daftar Karyawan & Dokter</strong> (per {$date}):<br>" . implode('<br>', $replies),
                'results'      => $cards,
                'action_url'   => route('karyawan'),
                'action_label' => 'Buka Manajemen Karyawan',
            ];
        }

        if ($toolName === 'search_layanan') {
            if (!($data['found'] ?? false)) {
                return ['reply' => $data['message'] ?? 'Layanan tidak ditemukan.', 'results' => []];
            }
            $grouped = $data['grouped'] ?? [];
            $replies = [];
            $cards   = [];
            foreach ($grouped as $jenis => $items) {
                $replies[] = "<strong>{$jenis}</strong>:";
                foreach ($items as $item) {
                    $replies[] = "&nbsp;&nbsp;- {$item['nama']}: <strong>{$item['harga_formatted']}</strong>";
                    $cards[]   = [
                        'title'       => $item['nama'],
                        'subtitle'    => "Jenis: {$item['jenis']} | Harga: {$item['harga_formatted']}",
                        'badge'       => $item['jenis'],
                        'badge_class' => 'bg-primary',
                        'url'         => route('layanan'),
                    ];
                }
            }
            return [
                'reply'        => "<strong>Tarif Layanan Klinik</strong> ({$data['total']} layanan):<br>" . implode('<br>', $replies),
                'results'      => $cards,
                'action_url'   => route('layanan'),
                'action_label' => 'Buka Manajemen Layanan',
            ];
        }

        if ($toolName === 'get_transaksi_log') {
            if (!($data['found'] ?? false)) {
                return ['reply' => $data['message'] ?? 'Tidak ada catatan pengambilan obat.', 'results' => []];
            }
            $items   = $data['items'] ?? [];
            $period  = $data['period'] ?? '';
            $replies = [];
            $cards   = [];
            foreach ($items as $item) {
                $replies[] = "- <strong>[{$item['kode_booking']}]</strong> {$item['owner']} / {$item['hewan']} : <strong>{$item['nama_barang']}</strong> x{$item['jumlah']} = <strong>{$item['total_formatted']}</strong> ({$item['tanggal']})";
                $cards[]   = [
                    'title'       => "{$item['nama_barang']} ({$item['total_formatted']})",
                    'subtitle'    => "Booking: {$item['kode_booking']} | Owner: {$item['owner']} | Hewan: {$item['hewan']} | Qty: {$item['jumlah']} | Tgl: {$item['tanggal']}",
                    'badge'       => 'Pengambilan Obat',
                    'badge_class' => 'bg-secondary',
                    'url'         => route('booking-uncompleted'),
                ];
            }
            $totalNilai = $data['total_nilai_formatted'] ?? '-';
            return [
                'reply'        => "<strong>Catatan Pengambilan Obat</strong> ({$period}) : Total Nilai: <strong>{$totalNilai}</strong>:<br>" . implode('<br>', $replies),
                'results'      => $cards,
                'action_url'   => route('booking-uncompleted'),
                'action_label' => 'Buka Manajemen Booking',
            ];
        }

        if ($toolName === 'compare_omzet') {
            if (!($data['found'] ?? false)) {
                return ['reply' => 'Data omzet tidak ditemukan.', 'results' => []];
            }
            $a       = $data['period_a'];
            $b       = $data['period_b'];
            $trend   = $data['trend'] ?? 'sama';
            $trendLabel = $trend === 'naik' ? '<strong style="color:#16a34a">naik</strong>'
                        : ($trend === 'turun' ? '<strong style="color:#dc2626">turun</strong>' : 'sama');
            $pct     = $data['persentase'] ?? 0;
            $selisih = $data['selisih_formatted'] ?? '-';

            $reply = "<strong>Perbandingan Omzet</strong>:<br>"
                   . "- <strong>{$a['label']}</strong>: {$a['total_formatted']} ({$a['count']} kunjungan)<br>"
                   . "- <strong>{$b['label']}</strong>: {$b['total_formatted']} ({$b['count']} kunjungan)<br>"
                   . "Selisih: <strong>{$selisih}</strong> ({$trendLabel} {$pct}%).<br>"
                   . $data['summary'];

            return [
                'reply'        => $reply,
                'results'      => [[
                    'title'       => "Perbandingan: {$a['label']} vs {$b['label']}",
                    'subtitle'    => "{$a['total_formatted']} vs {$b['total_formatted']} | Selisih: {$selisih} ({$pct}%)",
                    'badge'       => ucfirst($trend),
                    'badge_class' => $trend === 'naik' ? 'bg-success' : ($trend === 'turun' ? 'bg-danger' : 'bg-secondary'),
                    'url'         => route('laporan-omzet.index'),
                ]],
                'action_url'   => route('laporan-omzet.index'),
                'action_label' => 'Buka Laporan Omzet',
            ];
        }

        if ($toolName === 'get_temporary_invoice') {
            if (!($data['found'] ?? false)) {
                return ['reply' => 'Nota estimasi biaya pre-billing tidak ditemukan.', 'results' => []];
            }
            $invoices = $data['invoices'] ?? [];
            $replies  = [];
            $cards    = [];
            foreach ($invoices as $inv) {
                $replies[] = "- <strong>[{$inv['kode_estimasi']}]</strong> Owner: <strong>{$inv['nama_owner']}</strong> | Hewan: <strong>{$inv['nama_hewan']}</strong> | Total: <strong>{$inv['grand_total_formatted']}</strong>";
                $cards[]   = [
                    'title'       => "[{$inv['kode_estimasi']}] {$inv['nama_owner']} / {$inv['nama_hewan']}",
                    'subtitle'    => "Grand Total: <strong>{$inv['grand_total_formatted']}</strong>",
                    'badge'       => 'Estimasi Biaya',
                    'badge_class' => 'bg-info text-dark',
                    'url'         => $inv['url'],
                ];
            }
            return [
                'reply'        => "<strong>Nota Estimasi Biaya</strong> ({$data['total']} data):<br>" . implode('<br>', $replies),
                'results'      => $cards,
                'action_url'   => route('estimasi-biaya.index'),
                'action_label' => 'Buka Estimasi Biaya',
            ];
        }

        if ($toolName === 'get_booking_detail') {
            if ($data['status'] === 'not_found') {
                return ['reply' => $data['message'] ?? 'Booking tidak ditemukan.', 'results' => []];
            }
            $b      = $data['booking']   ?? [];
            $owner  = $data['owner']     ?? [];
            $hewan  = $data['hewan']     ?? [];
            $dokter = $data['dokter']    ?? [];
            $vital  = $data['tanda_vital'] ?? [];
            $soap   = $data['emr_soap']  ?? [];
            $bill   = $data['billing']   ?? [];

            $layananList = '';
            foreach ($data['layanan'] ?? [] as $l) {
                $layananList .= "<br>&nbsp;&nbsp;- {$l['nama']} (x{$l['qty']} @ {$l['harga']})";
            }
            $transaksiList = '';
            foreach ($data['transaksi'] ?? [] as $t) {
                $transaksiList .= "<br>&nbsp;&nbsp;- {$t['barang']} x{$t['jumlah']} = {$t['total']}";
            }

            $reply = "<strong>Detail Booking [{$b['kode_booking']}]</strong><br>"
                   . "Tanggal: <strong>{$b['tanggal']}</strong> {$b['jam']} | Tipe: {$b['tipe_kedatangan']} | Status: <strong>{$b['status']}</strong><br>"
                   . "Owner: <strong>{$owner['nama']}</strong> (Telp: {$owner['telpon']})<br>"
                   . "Pasien: <strong>{$hewan['nama']}</strong> ({$hewan['jenis']}, {$hewan['berat']})<br>"
                   . "Dokter: <strong>{$dokter['utama']}</strong><br>"
                   . "<br><strong>Tanda Vital:</strong> Suhu {$vital['suhu_tubuh']} | Denyut {$vital['denyut_jantung']} | CRT {$vital['crt']}<br>"
                   . "<br><strong>SOAP:</strong><br>"
                   . "&nbsp;&nbsp;S: " . ($soap['subjective'] !== '-' ? $soap['subjective'] : '-') . "<br>"
                   . "&nbsp;&nbsp;O: " . ($soap['objective']  !== '-' ? $soap['objective']  : '-') . "<br>"
                   . "&nbsp;&nbsp;A: <strong>" . ($soap['assessment'] !== '-' ? $soap['assessment'] : '-') . "</strong><br>"
                   . "&nbsp;&nbsp;P: " . ($soap['plan']       !== '-' ? $soap['plan']       : '-') . "<br>"
                   . ($layananList  ? "<br><strong>Layanan:</strong>{$layananList}" : '')
                   . ($transaksiList ? "<br><strong>Obat Diambil:</strong>{$transaksiList}" : '')
                   . "<br><br><strong>Billing:</strong> Total {$bill['total']} | Diskon {$bill['diskon']} | Pajak {$bill['pajak']} | Metode: {$bill['metode_pembayaran']}";

            return [
                'reply'        => $reply,
                'results'      => [[
                    'title'       => "[{$b['kode_booking']}] {$hewan['nama']} ({$owner['nama']})",
                    'subtitle'    => "Status: {$b['status']} | Total: {$bill['total']} | Dokter: {$dokter['utama']}",
                    'badge'       => $b['status'],
                    'badge_class' => 'bg-primary',
                    'url'         => route('booking-uncompleted'),
                ]],
                'action_url'   => route('booking-uncompleted'),
                'action_label' => 'Buka Manajemen Booking',
            ];
        }

        if ($toolName === 'get_emr') {
            if ($data['status'] === 'not_found') {
                return ['reply' => $data['message'] ?? 'Rekam medis tidak ditemukan.', 'results' => []];
            }
            $records = $data['records'] ?? [];
            $replies = [];
            $cards   = [];
            foreach ($records as $r) {
                $vital = $r['tanda_vital'] ?? [];
                $soap  = $r['soap']        ?? [];
                $replies[] = "<strong>[{$r['kode_booking']}] {$r['hewan']} ({$r['jenis_hewan']}) : {$r['tanggal']}</strong><br>"
                           . "&nbsp;&nbsp;Dokter: {$r['dokter']}<br>"
                           . "&nbsp;&nbsp;Vital: Suhu {$vital['suhu_tubuh']} | Denyut {$vital['denyut_jantung']} | BB {$vital['berat']}<br>"
                           . "&nbsp;&nbsp;S: " . ($soap['subjective'] !== '-' ? $soap['subjective'] : '-') . "<br>"
                           . "&nbsp;&nbsp;A: <strong>" . ($soap['assessment'] !== '-' ? $soap['assessment'] : 'Belum diisi') . "</strong><br>"
                           . "&nbsp;&nbsp;P: " . ($soap['plan']       !== '-' ? $soap['plan']       : '-');
                $cards[] = [
                    'title'       => "[{$r['kode_booking']}] {$r['hewan']} ({$r['tanggal']})",
                    'subtitle'    => "Diagnosa: " . ($soap['assessment'] ?: '-') . " | Dokter: {$r['dokter']}",
                    'badge'       => 'Rekam Medis',
                    'badge_class' => 'bg-info text-dark',
                    'url'         => route('booking-uncompleted'),
                ];
            }
            $total = $data['total'] ?? count($records);
            return [
                'reply'        => "<strong>Rekam Medis ({$total} kunjungan):</strong><br><br>" . implode('<br><br>', $replies),
                'results'      => $cards,
                'action_url'   => route('booking-uncompleted'),
                'action_label' => 'Buka Manajemen Booking',
            ];
        }

        // Generic fallback : should not happen for known tools
        return ['reply' => 'Data berhasil diambil namun format tidak dikenali.', 'results' => []];
    }

    /**
     * Domain-specific fast path intent resolution when a specialist skill mode is active.
     */
    protected function resolveSpecialistIntent(string $rawMsg, string $lowerMsg, string $activeSkill): ?array
    {
        switch ($activeSkill) {
            case 'karyawan':
                $date = date('Y-m-d');
                if (preg_match('/(\d{4}-\d{2}-\d{2})/', $rawMsg, $m)) {
                    $date = $m[1];
                }
                $clean = trim(preg_replace('/^(?:siapa|daftar|cek|lihat|jadwal|staf|staff|dokter|karyawan|yang jaga|jaga|bertugas|hari ini)\s+/ui', '', $rawMsg));
                $clean = trim(preg_replace('/\b(?:hari ini|dokter|staf|staff|karyawan|jaga)\b/ui', '', $clean));
                return [
                    'tool_name'  => 'get_staff',
                    'parameters' => [
                        'query' => $clean,
                        'date'  => $date,
                    ],
                ];

            case 'owner':
                $clean = trim(preg_replace('/^(?:cari|lihat|cek|data|profil|informasi|tentang)\s+/ui', '', $rawMsg));
                return [
                    'tool_name'  => 'search_owner',
                    'parameters' => ['query' => $clean ?: $rawMsg],
                ];

            case 'inventori':
                $clean = trim(preg_replace('/^(?:cek|lihat|cari|stok|obat|ketersediaan|harga)\s+/ui', '', $rawMsg));
                $clean = trim(preg_replace('/\b(?:stok|obat)\b/ui', '', $clean));
                return [
                    'tool_name'  => 'search_inventory',
                    'parameters' => ['query' => $clean ?: $rawMsg],
                ];

            case 'emr':
                $kode = '';
                if (preg_match('/\b(BOOK[A-Z0-9]{5,})\b/i', $rawMsg, $m)) {
                    $kode = strtoupper($m[1]);
                }
                $clean = trim(preg_replace('/^(?:rekam medis|emr|soap|diagnosa|riwayat|pasien)\s+/ui', '', $rawMsg));
                return [
                    'tool_name'  => 'get_emr',
                    'parameters' => [
                        'pet'          => $clean,
                        'kode_booking' => $kode,
                    ],
                ];

            case 'booking':
                if (str_contains($lowerMsg, 'hari ini') || str_contains($lowerMsg, 'sekarang') || str_contains($lowerMsg, 'antrean')) {
                    return ['tool_name' => 'get_today_appointments', 'parameters' => []];
                }
                $date = date('Y-m-d');
                if (preg_match('/(\d{4}-\d{2}-\d{2})/', $rawMsg, $m)) {
                    $date = $m[1];
                }
                return [
                    'tool_name'  => 'get_appointments',
                    'parameters' => ['date' => $date],
                ];

            case 'laporan':
                if (str_contains($lowerMsg, 'bandingkan') || str_contains($lowerMsg, 'dibanding') || str_contains($lowerMsg, ' vs ')) {
                    $months = $this->extractMonthsFromMessage($rawMsg);
                    return [
                        'tool_name'  => 'compare_omzet',
                        'parameters' => [
                            'period_a' => $months[0] ?? 'last_month',
                            'period_b' => $months[1] ?? 'this_month',
                        ],
                    ];
                }
                $period = 'this_month';
                if (str_contains($lowerMsg, 'hari ini')) $period = 'today';
                elseif (str_contains($lowerMsg, 'bulan lalu')) $period = 'last_month';
                return [
                    'tool_name'  => 'get_omzet_report',
                    'parameters' => ['period' => $period],
                ];

            case 'layanan':
                $clean = trim(preg_replace('/^(?:tarif|biaya|harga|layanan|jasa|cek|cari)\s+/ui', '', $rawMsg));
                return [
                    'tool_name'  => 'search_layanan',
                    'parameters' => ['query' => $clean],
                ];
        }

        return null;
    }

    /**
     * Wrap orchestrator response with active modular skill metadata.
     */
    protected function wrapResponseWithSkill(array $response): array
    {
        $activeSkillKey = $this->contextManager->getActiveSkill();
        if ($activeSkillKey) {
            $skillMeta = AISkillManager::getSkill($activeSkillKey);
            $response['active_skill'] = $activeSkillKey;
            $response['skill_title']  = $skillMeta['title'] ?? ucfirst($activeSkillKey);
            $response['skill_color']  = $skillMeta['color'] ?? '#2d8aba';
            $response['skill_badge']  = $skillMeta['badge_class'] ?? 'bg-primary';

            if (empty($response['suggestions']) && !empty($skillMeta['quick_chips'])) {
                $response['suggestions'] = $skillMeta['quick_chips'];
            }
        } else {
            $response['active_skill'] = null;
            $response['skill_title']  = null;
            $response['skill_color']  = null;
            $response['skill_badge']  = null;
        }

        return $response;
    }
}
