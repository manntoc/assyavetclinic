<?php

namespace App\AI;

use App\Models\AIConversation;
use App\Models\AIMessage;
use App\Models\User;

class AIContextManager
{
    protected ?AIConversation $conversation = null;

    /**
     * Get or create active conversation for user.
     */
    public function getOrCreateConversation(?User $user, ?int $conversationId = null): AIConversation
    {
        if ($conversationId) {
            $conversation = AIConversation::find($conversationId);
            if ($conversation) {
                $this->conversation = $conversation;
                return $conversation;
            }
        }

        $userId = $user ? $user->id : null;
        $staleTtlHours = config('ai.conversation_ttl_hours', 4);

        $conversation = AIConversation::where('user_id', $userId)
            ->where('status', 'active')
            ->latest()
            ->first();

        // Close stale conversation; force new session
        if ($conversation && $conversation->updated_at->diffInHours(now()) >= $staleTtlHours) {
            $conversation->update(['status' => 'closed']);
            $conversation = null;
        }

        if (!$conversation) {
            $conversation = AIConversation::create([
                'user_id' => $userId,
                'title' => 'Percakapan ' . date('d M Y H:i'),
                'status' => 'active',
                'state' => AIStateMachine::IDLE,
                'context_data' => []
            ]);
        }

        $this->conversation = $conversation;
        return $conversation;
    }

    /**
     * Add a message to current conversation.
     */
    public function addMessage(string $role, string $content, array $metadata = []): AIMessage
    {
        if (!$this->conversation) {
            throw new \Exception("No active conversation set in ContextManager.");
        }

        return AIMessage::create([
            'conversation_id' => $this->conversation->id,
            'role' => $role,
            'content' => $content,
            'metadata' => $metadata
        ]);
    }

    /**
     * Update state of conversation.
     */
    public function updateState(string $newState): void
    {
        if ($this->conversation && AIStateMachine::isValidState($newState)) {
            $this->conversation->update(['state' => $newState]);
        }
    }

    /**
     * Update/Merge context data (collected parameters/entities).
     */
    public function mergeContextData(array $newEntities): void
    {
        if ($this->conversation) {
            $existing = $this->conversation->context_data ?? [];
            $merged = array_merge($existing, array_filter($newEntities, function($val) {
                return $val !== null && $val !== '';
            }));
            $this->conversation->update(['context_data' => $merged]);
        }
    }

    /**
     * Reset context data to empty array (optionally preserving active_skill).
     */
    public function resetContextData(bool $preserveSkill = true): void
    {
        if ($this->conversation) {
            $skill = $preserveSkill ? $this->getActiveSkill() : null;
            $newContext = $skill ? ['active_skill' => $skill] : [];
            $this->conversation->update(['context_data' => $newContext, 'state' => AIStateMachine::IDLE]);
        }
    }

    /**
     * Get the active modular skill / agent mode if any.
     */
    public function getActiveSkill(): ?string
    {
        if (!$this->conversation) return null;
        $context = $this->conversation->context_data ?? [];
        return $context['active_skill'] ?? null;
    }

    /**
     * Set or clear the active modular skill / agent mode.
     */
    public function setActiveSkill(?string $skill): void
    {
        if (!$this->conversation) return;
        $context = $this->conversation->context_data ?? [];
        if ($skill === null || $skill === 'general' || $skill === '') {
            unset($context['active_skill']);
        } else {
            $context['active_skill'] = strtolower(trim($skill));
        }
        $this->conversation->update(['context_data' => $context]);
    }

    /**
     * Extract named entities (owner, pet, obat, tanggal) from user message
     * and merge into context_data['entities'] for cross-turn memory.
     */
    public function extractAndStoreEntities(string $msg): void
    {
        if (!$this->conversation) return;
        $entities = ($this->conversation->context_data ?? [])['entities'] ?? [];

        $lower = strtolower($msg);

        // Owner name : "owner Budi", "pemilik Siti", "milik Andi"
        if (preg_match('/(?:owner|pemilik|milik)\s+([A-Za-z][A-Za-z\s]{1,30}?)(?:\s|$|,|\.|dengan|hewan|pet)/ui', $msg, $m)) {
            $entities['owner'] = trim($m[1]);
        }

        // Pet/hewan name : "kucing Milo", "anjing Rex", "hewan Mochi", "pasien Kiki"
        if (preg_match('/(?:kucing|anjing|burung|kelinci|hewan|pasien|pet)\s+([A-Za-z][A-Za-z0-9\s]{1,20}?)(?:\s|$|,|\.|owner|milik)/ui', $msg, $m)) {
            $entities['pet'] = trim($m[1]);
        }

        // Medicine/obat name : "obat Amoxicillin", "stok Acidurin"
        if (preg_match('/(?:obat|stok|cek)\s+([A-Za-z][A-Za-z0-9\s\-]{2,30}?)(?:\s|$|,|\.)/ui', $msg, $m)) {
            $entities['obat'] = trim($m[1]);
        }

        // Date : YYYY-MM-DD or D/M/YYYY
        if (preg_match('/(\d{4}-\d{2}-\d{2})/', $msg, $m)) {
            $entities['tanggal'] = $m[1];
        } elseif (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/', $msg, $m)) {
            $entities['tanggal'] = "{$m[3]}-" . str_pad($m[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
        }

        // Booking code
        if (preg_match('/\b(BOOK[A-Z0-9]{5,})\b/i', $msg, $m)) {
            $entities['booking_code'] = strtoupper($m[1]);
        }

        if (empty($entities)) return;

        $data = $this->conversation->context_data ?? [];
        $data['entities'] = $entities;
        $this->conversation->update(['context_data' => $data]);
    }

    /**
     * Build a short context string from stored entities for injection into system prompt.
     */
    public function getEntityContext(): string
    {
        if (!$this->conversation) return '';
        $entities = ($this->conversation->context_data ?? [])['entities'] ?? [];
        if (empty($entities)) return '';

        $parts = [];
        if (!empty($entities['owner']))        $parts[] = "Owner aktif: {$entities['owner']}";
        if (!empty($entities['pet']))          $parts[] = "Hewan aktif: {$entities['pet']}";
        if (!empty($entities['obat']))         $parts[] = "Obat/item aktif: {$entities['obat']}";
        if (!empty($entities['tanggal']))      $parts[] = "Tanggal aktif: {$entities['tanggal']}";
        if (!empty($entities['booking_code'])) $parts[] = "Kode booking aktif: {$entities['booking_code']}";

        return '[Konteks sesi aktif]: ' . implode(' | ', $parts);
    }

    /**
     * Store a brief summary of the last tool result for cross-turn reference.
     * Stored under context_data['last_results'][tool_name].
     */
    public function storeToolResult(string $toolName, string $summary): void
    {
        if (!$this->conversation) return;
        $data = $this->conversation->context_data ?? [];
        $data['last_results'][$toolName] = $summary;
        $this->conversation->update(['context_data' => $data]);
    }

    /**
     * Store an omzet period queried by user (YYYY-MM, 'year', etc.).
     * Keeps a sliding window of the last 5 queried periods.
     */
    public function storeOmzetPeriod(string $period): void
    {
        if (!$this->conversation) return;
        $data    = $this->conversation->context_data ?? [];
        $periods = $data['omzet_periods'] ?? [];
        // Avoid duplicates in sequence
        if (end($periods) !== $period) {
            $periods[] = $period;
        }
        // Keep last 5
        $data['omzet_periods'] = array_slice($periods, -5);
        $this->conversation->update(['context_data' => $data]);
    }

    /**
     * Return the last N distinct omzet periods queried in this session.
     * Used for context-aware compare resolution ("kedua bulan itu").
     *
     * @return string[]
     */
    public function getRecentOmzetPeriods(int $limit = 2): array
    {
        if (!$this->conversation) return [];
        $periods = ($this->conversation->context_data ?? [])['omzet_periods'] ?? [];
        return array_slice($periods, -$limit);
    }

    /**
     * Return current conversation ID (or null).
     */
    public function getConversationId(): ?int
    {
        return $this->conversation?->id;
    }

    /**
     * Count total messages in current conversation.
     */
    public function getMessageCount(): int
    {
        if (!$this->conversation) return 0;
        return AIMessage::where('conversation_id', $this->conversation->id)->count();
    }

    /**
     * Build a short context string from stored last tool results for injection into system prompt.
     */
    public function getLastResultsContext(): string
    {
        if (!$this->conversation) return '';
        $lastResults = ($this->conversation->context_data ?? [])['last_results'] ?? [];
        if (empty($lastResults)) return '';

        $parts = [];
        foreach ($lastResults as $tool => $summary) {
            $parts[] = "[Hasil {$tool}]: {$summary}";
        }
        return '[Hasil tool terakhir]: ' . implode(' | ', $parts);
    }

    /**
     * Persist a conversation summary string into context_data['summary'].
     */
    public function storeSummary(string $summary): void
    {
        if (!$this->conversation) return;
        $data = $this->conversation->context_data ?? [];
        $data['summary'] = $summary;
        $this->conversation->update(['context_data' => $data]);
    }

    /**
     * Retrieve recent message history formatted for LLM.
     * If a summary exists, prepend it as a system message before the sliding window.
     */
    public function getFormattedMessageHistory(int $limit = 10): array
    {
        if (!$this->conversation) return [];

        $messages = AIMessage::where('conversation_id', $this->conversation->id)
            ->latest('id')
            ->take($limit)
            ->get()
            ->reverse();

        $history = [];

        // Inject summary as context if available (Summarization Memory : Metode 3)
        $summary = ($this->conversation->context_data ?? [])['summary'] ?? null;
        if ($summary) {
            $history[] = [
                'role'    => 'system',
                'content' => '[Ringkasan percakapan sebelumnya]: ' . $summary,
            ];
        }

        foreach ($messages as $msg) {
            $history[] = [
                'role'    => $msg->role === 'tool' ? 'user' : $msg->role,
                'content' => $msg->content
            ];
        }

        return $history;
    }
}
