<?php

namespace App\Services\Instagram;

use App\Services\NineRouterService;
use Illuminate\Support\Facades\Log;

/**
 * Orchestrates AI content generation for Instagram carousel.
 * Uses existing NineRouterService — no new AI client.
 */
class InstagramContentService
{
    protected NineRouterService $ai;

    public function __construct(NineRouterService $ai)
    {
        $this->ai = $ai;
    }

    /**
     * Generate full carousel structure from topic + source data.
     *
     * @return array{slides: array, caption: string, hashtags: array}
     * @throws \Exception on AI failure or invalid JSON
     */
    public function generateCarousel(
        string $topic,
        string $contentType,
        string $tone,
        int $slideCount,
        string $templateStyle,
        array $sourceData = [],
        ?string $clinicName = null
    ): array {
        $brand = InstagramBrandResolver::resolve();
        $clinicName ??= $brand['clinic_name'];

        $systemPrompt = $this->buildSystemPrompt($clinicName);
        $userPrompt   = $this->buildUserPrompt(
            $topic, $contentType, $tone, $slideCount, $templateStyle, $sourceData, $clinicName
        );

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user',   'content' => $userPrompt],
        ];

        $response = $this->ai->chatCompletion($messages, $this->resolveModel(), [
            'temperature' => 0.7,
            'max_tokens'  => 2500,
        ]);

        $rawText = $response['choices'][0]['message']['content'] ?? '';

        return $this->parseAndValidate($rawText, $slideCount);
    }

    /**
     * Regenerate a single slide without changing others.
     */
    public function regenerateSlide(
        string $topic,
        string $purpose,
        string $contentType,
        string $tone,
        string $layout,
        ?string $clinicName = null
    ): array {
        $brand = InstagramBrandResolver::resolve();
        $clinicName ??= $brand['clinic_name'];

        $systemPrompt = $this->buildSystemPrompt($clinicName);
        $userPrompt   = <<<PROMPT
Regenerasi konten untuk SATU slide Instagram klinik hewan.

PENTING — LOCKED (JANGAN DIUBAH):
- purpose: "{$purpose}"  ← WAJIB tetap sama persis
- layout: "{$layout}"    ← WAJIB tetap sama persis

Spesifikasi slide:
- Topik: {$topic}
- Tujuan slide: {$purpose}
- Tipe konten: {$contentType}
- Nada: {$tone}
- Layout: {$layout}
- Nama klinik: {$clinicName}

Yang boleh berubah: headline, body, image_prompt (konten baru, segar, tidak monoton).
Yang TIDAK boleh berubah: purpose, layout.

Balas HANYA JSON satu objek (tanpa array luar, tanpa teks lain):
{
  "slide_number": 1,
  "purpose": "{$purpose}",
  "headline": "...",
  "body": "...",
  "image_prompt": "Deskripsi visual dalam bahasa Inggris, tanpa teks/logo di gambar",
  "layout": "{$layout}"
}
PROMPT;

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user',   'content' => $userPrompt],
        ];

        $response = $this->ai->chatCompletion($messages, $this->resolveModel(), [
            'temperature' => 0.75,
            'max_tokens'  => 600,
        ]);

        $rawText = $response['choices'][0]['message']['content'] ?? '';
        $slide = $this->extractJson($rawText);

        if (!isset($slide['headline'])) {
            throw new \Exception('AI menghasilkan format slide tidak valid.');
        }

        // Server-side guarantee: AI tidak boleh mengubah purpose & layout.
        // Override apapun yang AI kembalikan dengan nilai yang dikirim.
        $slide['purpose'] = $purpose;
        $slide['layout']  = $layout;

        return $slide;
    }

    /**
     * Regenerate only caption + hashtags.
     */
    public function regenerateCaption(string $topic, array $slides, ?string $clinicName = null): array
    {
        $brand = InstagramBrandResolver::resolve();
        $clinicName ??= $brand['clinic_name'];

        $slideSummary = collect($slides)->map(fn($s, $i) =>
            ($i + 1) . '. [' . ($s['purpose'] ?? '') . '] ' . ($s['headline'] ?? '')
        )->implode("\n");

        $prompt = <<<PROMPT
Buat caption Instagram dan hashtag untuk konten klinik hewan "{$clinicName}".
Topik: {$topic}
Ringkasan slide:
{$slideSummary}

Aturan:
- Bahasa Indonesia, natural, tidak spam hashtag
- Caption: 2-4 kalimat + CTA, maksimal 200 kata
- Hashtag: 10-15 hashtag relevan (campuran besar dan niche)
- Balas HANYA JSON:
{"caption": "...", "hashtags": ["#...", "#..."]}
PROMPT;

        $response = $this->ai->chatCompletion(
            [['role' => 'user', 'content' => $prompt]],
            $this->resolveModel(),
            ['temperature' => 0.6, 'max_tokens' => 500]
        );

        $raw = $response['choices'][0]['message']['content'] ?? '{}';
        $data = $this->extractJson($raw);

        return [
            'caption'  => $data['caption']  ?? '',
            'hashtags' => $data['hashtags'] ?? [],
        ];
    }

    // -------------------------------------------------------------------------
    // Model resolution — pick a working model from 9Router
    // -------------------------------------------------------------------------

    /**
     * Return a model ID that actually exists on this 9Router instance.
     * Falls back from configured default → kryptous-project → first available.
     */
    protected function resolveModel(): string
    {
        $configured = config('ai.default_model', '');

        // Prefer configured if it looks like a 9Router-style ID (not bare "gpt-4o")
        if ($configured && str_contains($configured, '/')) {
            return $configured;
        }

        // Try to get model list; pick first available
        try {
            $list = $this->ai->getModels();
            $models = $list['data'] ?? [];
            if (!empty($models)) {
                // Prefer kryptous-project if present (project default)
                foreach ($models as $m) {
                    if (($m['id'] ?? '') === 'kryptous-project') return 'kryptous-project';
                }
                return $models[0]['id'];
            }
        } catch (\Exception) {
            // ignore — fall through to hardcoded default
        }

        // Last resort: use configured value even if bare name
        return $configured ?: 'kryptous-project';
    }

    // -------------------------------------------------------------------------
    // Prompt builders
    // -------------------------------------------------------------------------

    private function buildSystemPrompt(string $clinicName): string
    {
        return <<<PROMPT
Anda adalah content creator profesional untuk klinik hewan "{$clinicName}".
Tugas Anda membuat konten Instagram carousel edukatif dan informatif untuk pemilik hewan peliharaan.
Gaya desain mengikuti standar desainer Assyavet Clinic: clean, ramah, interaktif, dan mudah dibaca.

ATURAN STRUKTUR TEKS:
1. Slide 1 (Hook): Headline berupa PERTANYAAN atau FAKTA SINGKAT (max 6-8 kata). Contoh: "Anjing Bisa Cemburu?", "Scabies: Kudis Menular pada Anabul". Body sangat singkat (1 kalimat).
2. Slide 2-4 (Edukasi/Tips/Gejala): Gunakan format POIN-POIN SINGKAT (2-3 poin per slide, dipisahkan baris baru). Jangan buat paragraf panjang. Contoh:
   "No. 1: Jaga kebersihan area tidur
   No. 2: Vaksinasi rutin tepat waktu
   No. 3: Periksakan ke dokter saat mulai gatal"
3. Slide Terakhir (CTA): Headline ajakan ramah menggunakan nama klinik yang diberikan: "{$clinicName}". Contoh: "Yuk Konsultasikan ke {$clinicName}!". Body: ajakan singkat periksa anabul.
4. Bahasa Indonesia santai tapi profesional, hindari istilah medis yang membingungkan.
5. JANGAN mengarang data pasien, dosis obat, atau jaminan kesembuhan.
6. Balas HANYA dalam format JSON yang diminta, tanpa teks tambahan di luar JSON.
PROMPT;
    }

    private function buildUserPrompt(
        string $topic,
        string $contentType,
        string $tone,
        int $slideCount,
        string $templateStyle,
        array $sourceData,
        string $clinicName
    ): string {
        $purposes = $this->purposesForCount($slideCount);
        $purposeList = implode(', ', $purposes);

        $sourceBlock = '';
        if (!empty($sourceData['data'])) {
            $encoded = json_encode($sourceData['data'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $sourceBlock = "\n\nDATA SUMBER KLINIK (gunakan sebagai konteks, jangan tampilkan data PII):\n```json\n{$encoded}\n```";
        }

        $templateGuide = $this->templateGuide($templateStyle);

        $layoutMatrix = $this->buildLayoutMatrix($contentType, $templateStyle);

        return <<<PROMPT
Buat carousel Instagram ({$slideCount} slide) untuk klinik hewan "{$clinicName}".

Topik: {$topic}
Tipe konten: {$contentType}
Nada: {$tone}
Template: {$templateStyle} — {$templateGuide}
Tujuan per slide (urutan): {$purposeList}
{$sourceBlock}

Format respons WAJIB — JSON valid, tidak ada teks di luar JSON:
{
  "topic": "{$topic}",
  "content_type": "{$contentType}",
  "tone": "{$tone}",
  "slides": [
    {
      "slide_number": 1,
      "purpose": "hook",
      "headline": "Judul menarik singkat (max 8 kata)",
      "body": "Teks pendukung (2-3 kalimat max)",
      "image_prompt": "Deskripsi visual dalam bahasa Inggris untuk ilustrasi (jangan minta teks/logo di gambar)",
      "layout": "hook_center"
    }
  ],
  "caption": "Caption Instagram 2-4 kalimat + CTA",
  "hashtags": ["#KlinikHewan", "#DokterHewan"]
}

LAYOUT SELECTION MATRIX — gunakan panduan ini untuk memilih layout per slide:

{$layoutMatrix}

ATURAN LAYOUT TAMBAHAN:
1. Purpose "cta" WAJIB menggunakan layout "cta_full" — tidak ada pengecualian.
2. Purpose "hook" SANGAT DIREKOMENDASIKAN "hook_center" atau "typography_hero" untuk stopping power.
3. VARIASIKAN layout antar slide — jangan gunakan layout yang sama dua slide berturut-turut.
4. Jika konten slide sangat singkat/emotional → pilih layout low-density (hook_center, typography_hero, blue_gradient).
5. Jika konten slide berisi daftar poin/data → pilih layout high-density (navy_full, white_top_blue, card_grid).
6. Pilih berdasarkan ISI konten, bukan karena urutan slide semata.

Hasilkan tepat {$slideCount} slide dengan tujuan: {$purposeList}.
PROMPT;
    }

    /**
     * Return ordered purposes based on slide count.
     */
    private function purposesForCount(int $count): array
    {
        $all = ['hook', 'problem', 'solution', 'tips', 'cta'];
        if ($count === 1) return ['hook'];
        if ($count === 2) return ['hook', 'cta'];
        if ($count === 3) return ['hook', 'solution', 'cta'];
        if ($count === 4) return ['hook', 'problem', 'solution', 'cta'];
        return $all; // 5
    }

    private function templateGuide(string $template): string
    {
        return match ($template) {
            'hero'         => 'Visual dominan, teks minimal. Prioritaskan stopping power visual.',
            'text_focus'   => 'Typography sebagai focal point. Whitespace luas, tanpa visual besar.',
            'split'        => 'Dua zona visual — teks dan foto sejajar. Rasio fleksibel 60:40 atau 40:60.',
            'case_study'   => 'Clinical storytelling. Ikuti alur: kasus → temuan → tindakan → hasil.',
            'educational'  => 'Edukasi editorial. Gunakan poin bernomor, do/don\'t, atau step-by-step.',
            default        => 'Pilih layout berdasarkan tipe konten dan kebutuhan komunikasi setiap slide.',
        };
    }

    /**
     * Build a context-aware layout selection matrix for the AI prompt.
     * Matrix varies by content_type and template_style.
     */
    private function buildLayoutMatrix(string $contentType, string $templateStyle): string
    {
        // Layout catalog with purpose + visual density
        $catalog = <<<CATALOG
Layout yang tersedia (nama → kapan digunakan → density):
- hook_center    : Hook/cover — headline besar + foto persegi tengah. Visual dominant. [LOW density]
- typography_hero: Headline SANGAT besar mendominasi, foto kecil pojok. Statement kuat, impactful. [LOW density]
- blue_gradient  : Gradient biru elegan, foto/ikon bulat tengah. Transisi emosional, jembatan antar topik. [LOW density]
- editorial      : Nomor slide besar + garis + foto strip. Magazine style, informatif-visual. [MEDIUM density]
- split_reverse  : Foto besar kiri 40%, teks + poin kanan 60%. Cocok bila visual kuat tersedia. [MEDIUM density]
- scrapbook      : Sticky note, tape kuning, emoji hewan. Playful, konten ringan/lucu/relatable. [MEDIUM density]
- white_top_blue : Atas putih (headline+foto), panel biru bawah berisi poin "No. 1, 2, 3". [HIGH density]
- navy_full      : Full navy, teks putih, numbered list. Informasi medis padat, gejala, data klinis. [HIGH density]
- card_grid      : Grid 2-4 kartu. Tips perbandingan, checklist, FAQ. Tanpa foto wajib. [HIGH density]
- cta_full       : Slide penutup CTA. HANYA untuk purpose "cta". [SPECIAL]
CATALOG;

        // Per content_type recommendation
        $recommendations = match ($contentType) {
            'case_story' => <<<REC
PANDUAN UNTUK TIPE KONTEN "Kisah Kasus Klinik":
- Hook slide       → hook_center (visual kuat, pertanyaan/fakta mengejutkan)
- Problem/context  → split_reverse (foto kasus di kiri, deskripsi di kanan) atau editorial
- Findings/data    → navy_full (informasi klinis padat, numbered list)
- Solution/action  → white_top_blue (langkah tindakan terstruktur)
- Result/learning  → blue_gradient atau typography_hero (emotional, impactful)
- CTA              → cta_full (wajib)
REC,
            'educational', 'awareness' => <<<REC
PANDUAN UNTUK TIPE KONTEN EDUKASI / AWARENESS:
- Hook slide       → hook_center atau typography_hero (pertanyaan besar, fakta mengejutkan)
- Problem/context  → editorial atau blue_gradient (jembatan ke inti edukasi)
- Core education   → white_top_blue atau navy_full (poin bernomor, data)
- Tips/praktis     → card_grid (tips per kartu, checklist) atau white_top_blue
- Takeaway         → typography_hero atau blue_gradient (pesan kuat, mudah diingat)
- CTA              → cta_full (wajib)
REC,
            'tips' => <<<REC
PANDUAN UNTUK TIPE KONTEN TIPS PRAKTIS:
- Hook slide       → hook_center atau typography_hero (relatable situation, common mistake)
- Tips utama       → white_top_blue atau card_grid (numbered tips, scannable)
- Tips tambahan    → navy_full jika banyak poin, scrapbook jika ringan/playful
- Takeaway/summary → editorial atau blue_gradient
- CTA              → cta_full (wajib)
REC,
            'service' => <<<REC
PANDUAN UNTUK TIPE KONTEN LAYANAN KLINIK:
- Hook slide       → hook_center (visual layanan premium, headline benefit)
- Penjelasan       → split_reverse (foto fasilitas/tim kiri, deskripsi kanan)
- Keunggulan       → card_grid (2-3 keunggulan per kartu)
- Social proof     → editorial atau blue_gradient
- CTA              → cta_full (wajib)
REC,
            'promotional' => <<<REC
PANDUAN UNTUK TIPE KONTEN PROMO:
- Hook slide       → hook_center atau typography_hero (offer headline besar)
- Detail promo     → white_top_blue atau navy_full (syarat, benefit, periode)
- Urgency/deadline → typography_hero atau editorial
- CTA              → cta_full (wajib, headline ajakan kuat)
REC,
            'faq' => <<<REC
PANDUAN UNTUK TIPE KONTEN FAQ:
- Hook slide       → typography_hero atau hook_center (pertanyaan umum yang relatable)
- Setiap FAQ       → white_top_blue (Q di atas, A di panel bawah) atau editorial
- Summary FAQ      → card_grid (ringkasan poin penting)
- CTA              → cta_full (wajib)
REC,
            default => <<<REC
PANDUAN UMUM:
- Hook slide       → hook_center atau typography_hero
- Slide informatif → white_top_blue, navy_full, atau editorial tergantung density konten
- Slide transisi   → blue_gradient atau scrapbook
- CTA              → cta_full (wajib)
REC,
        };

        // Template override jika user pilih template spesifik (bukan auto)
        $templateOverride = '';
        if ($templateStyle !== 'auto') {
            $templateOverride = match ($templateStyle) {
                'hero'        => "\nOVERRIDE: User memilih template HERO — prioritaskan hook_center, split_reverse, blue_gradient. Kurangi card_grid dan navy_full.",
                'text_focus'  => "\nOVERRIDE: User memilih template TEXT FOCUS — prioritaskan navy_full, white_top_blue, typography_hero. Hindari split_reverse.",
                'split'       => "\nOVERRIDE: User memilih template SPLIT — gunakan split_reverse sebanyak mungkin. Variasikan dengan editorial.",
                'case_study'  => "\nOVERRIDE: User memilih template CASE STUDY — ikuti alur kasus klinis. Gunakan navy_full untuk data, split_reverse untuk visual kasus.",
                'educational' => "\nOVERRIDE: User memilih template EDUCATIONAL — gunakan card_grid dan white_top_blue untuk poin edukasi. Pastikan scannable.",
                default       => '',
            };
        }

        return $catalog . "\n\n" . $recommendations . $templateOverride;
    }

    // -------------------------------------------------------------------------
    // JSON extraction & validation
    // -------------------------------------------------------------------------

    private function parseAndValidate(string $rawText, int $expectedSlideCount): array
    {
        $data = $this->extractJson($rawText);

        if (empty($data['slides']) || !is_array($data['slides'])) {
            throw new \Exception('AI tidak menghasilkan slides. Raw: ' . substr($rawText, 0, 200));
        }

        // Ensure slide_number is sequential
        foreach ($data['slides'] as $i => &$slide) {
            $slide['slide_number'] = $i + 1;
            $slide['headline']     = $slide['headline'] ?? '';
            $slide['body']         = $slide['body']     ?? '';
            $slide['purpose']      = $slide['purpose']  ?? 'hook';
            $slide['layout']       = $slide['layout']   ?? 'hook_center';
            $slide['image_prompt'] = $slide['image_prompt'] ?? '';
        }
        unset($slide);

        return [
            'slides'   => array_slice($data['slides'], 0, $expectedSlideCount),
            'caption'  => $data['caption']  ?? '',
            'hashtags' => $data['hashtags'] ?? [],
            'raw'      => $data,
        ];
    }

    /**
     * Extract JSON from AI output that may contain surrounding prose.
     */
    private function extractJson(string $text): array
    {
        // Strip markdown code fences
        $text = preg_replace('/```(?:json)?\s*/', '', $text);
        $text = preg_replace('/```/', '', $text);
        $text = trim($text);

        // Find the outermost { } block
        $start = strpos($text, '{');
        $end   = strrpos($text, '}');

        if ($start === false || $end === false) {
            Log::warning('InstagramContentService: no JSON object found', ['raw' => substr($text, 0, 300)]);
            return [];
        }

        $jsonStr = substr($text, $start, $end - $start + 1);
        $decoded = json_decode($jsonStr, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('InstagramContentService: JSON parse error', [
                'error' => json_last_error_msg(),
                'raw'   => substr($jsonStr, 0, 300),
            ]);
            throw new \Exception('AI menghasilkan JSON tidak valid: ' . json_last_error_msg());
        }

        return $decoded;
    }
}
