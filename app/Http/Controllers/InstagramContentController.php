<?php

namespace App\Http\Controllers;

use App\Models\InstagramContent;
use App\Models\InstagramContentSlide;
use App\Models\InstagramPublication;
use App\Services\Instagram\InstagramBrandResolver;
use App\Services\Instagram\InstagramContentService;
use App\Services\Instagram\InstagramImageGenerator;
use App\Services\Instagram\InstagramPublisher;
use App\Services\Instagram\InstagramSourceResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InstagramContentController extends Controller
{
    public function __construct(
        protected InstagramContentService  $contentService,
        protected InstagramPublisher       $publisher,
        protected InstagramImageGenerator  $imageGenerator,
    ) {}

    // =========================================================================
    // Index — draft list
    // =========================================================================

    public function index(Request $request)
    {
        $query = InstagramContent::with('creator')
            ->orderByDesc('created_at');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_konten', 'like', "%{$search}%")
                  ->orWhere('topic', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $contents = $query->paginate(12)->withQueryString();

        return view('instagram_studio.index', compact('contents'));
    }

    // =========================================================================
    // Create form
    // =========================================================================

    public function create()
    {
        $sourceTypes       = InstagramSourceResolver::sourceTypes();
        $bookings          = InstagramSourceResolver::getSelectableBookings();
        $layanans          = InstagramSourceResolver::getSelectableLayanan();
        $brand             = InstagramBrandResolver::resolve();

        $contentTypes = [
            'educational' => 'Edukasi',
            'case_story'  => 'Kisah Kasus Klinik',
            'tips'        => 'Tips Praktis',
            'service'     => 'Layanan Klinik',
            'promotional' => 'Promo',
            'awareness'   => 'Awareness Kesehatan',
            'faq'         => 'FAQ Customer',
        ];

        $tones = [
            'professional' => 'Profesional',
            'friendly'     => 'Ramah & Santai',
            'informative'  => 'Informatif',
        ];

        $templates = [
            'auto'        => 'Otomatis (AI pilihkan)',
            'hero'        => 'A — Hero Image',
            'text_focus'  => 'B — Text Focus',
            'split'       => 'C — Split Layout',
            'case_study'  => 'D — Case Study',
            'educational' => 'E — Educational',
        ];

        return view('instagram_studio.create', compact(
            'sourceTypes', 'bookings', 'layanans', 'brand',
            'contentTypes', 'tones', 'templates'
        ));
    }

    // =========================================================================
    // Generate — AI call, create record + slides
    // =========================================================================

    public function generate(Request $request)
    {
        $request->validate([
            'source_type'    => 'required|in:kasus_klinik,edukasi,layanan,promo,custom',
            'source_id'      => 'nullable|integer',
            'topic'          => 'required|string|max:200',
            'content_type'   => 'required|string',
            'tone'           => 'required|string',
            'slide_count'    => 'required|integer|min:1|max:5',
            'template_style' => 'required|string',
        ]);

        try {
            $sourceData = InstagramSourceResolver::resolve(
                $request->source_type,
                $request->source_id ? (int) $request->source_id : null
            );

            $generated = $this->contentService->generateCarousel(
                topic:         $request->topic,
                contentType:   $request->content_type,
                tone:          $request->tone,
                slideCount:    (int) $request->slide_count,
                templateStyle: $request->template_style,
                sourceData:    $sourceData
            );

            $brand = InstagramBrandResolver::resolve();

            // Save content record
            $content = InstagramContent::create([
                'kode_konten'    => InstagramContent::generateKode(),
                'source_type'    => $request->source_type,
                'source_id'      => $request->source_id,
                'topic'          => $request->topic,
                'content_type'   => $request->content_type,
                'tone'           => $request->tone,
                'template_style' => $request->template_style,
                'slide_count'    => count($generated['slides']),
                'status'         => 'generated',
                'caption'        => $generated['caption'],
                'hashtags'       => $generated['hashtags'],
                'ai_raw_response' => $generated['raw'] ?? [],
                'brand_snapshot'  => $brand,
                'created_by'     => Auth::id(),
            ]);

            // Save slides
            foreach ($generated['slides'] as $slideData) {
                InstagramContentSlide::create([
                    'instagram_content_id' => $content->id,
                    'slide_number'         => $slideData['slide_number'],
                    'purpose'              => $slideData['purpose'],
                    'layout'               => $slideData['layout'],
                    'headline'             => $slideData['headline'],
                    'body'                 => $slideData['body'],
                    'design_data'          => ['image_prompt' => $slideData['image_prompt'] ?? ''],
                ]);
            }

            return redirect()->route('instagram-studio.preview', $content->id)
                ->with('success', 'Konten Instagram berhasil digenerate!');

        } catch (\Exception $e) {
            Log::error('InstagramContentController@generate: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal generate konten: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // Preview / Editor
    // =========================================================================

    public function preview(int $id)
    {
        $content = InstagramContent::with(['slides', 'creator'])->findOrFail($id);
        $brand   = $content->brand_snapshot ?? InstagramBrandResolver::resolve();

        return view('instagram_studio.preview', compact('content', 'brand'));
    }

    // =========================================================================
    // Update slide (inline edit)
    // =========================================================================

    public function updateSlide(Request $request, int $contentId, int $slideId)
    {
        $slide = InstagramContentSlide::where('id', $slideId)
            ->where('instagram_content_id', $contentId)
            ->firstOrFail();

        $request->validate([
            'headline' => 'nullable|string|max:200',
            'body'     => 'nullable|string|max:1000',
            'layout'   => 'nullable|string',
        ]);

        $slide->update($request->only('headline', 'body', 'layout'));

        return response()->json(['success' => true, 'slide' => $slide]);
    }

    // =========================================================================
    // Update caption/hashtags
    // =========================================================================

    public function updateCaption(Request $request, int $id)
    {
        $content = InstagramContent::findOrFail($id);

        $request->validate([
            'caption'  => 'nullable|string',
            'hashtags' => 'nullable|string', // comma/space/newline separated
        ]);

        $hashtagInput = $request->hashtags ?? '';
        $hashtags = collect(preg_split('/[\s,]+/', $hashtagInput))
            ->filter()
            ->map(fn($h) => str_starts_with($h, '#') ? $h : '#' . $h)
            ->values()
            ->toArray();

        $content->update([
            'caption'  => $request->caption,
            'hashtags' => $hashtags,
        ]);

        return response()->json(['success' => true]);
    }

    // =========================================================================
    // Regenerate single slide
    // =========================================================================

    public function regenerateSlide(Request $request, int $contentId, int $slideId)
    {
        $content = InstagramContent::findOrFail($contentId);
        $slide   = InstagramContentSlide::where('id', $slideId)
            ->where('instagram_content_id', $contentId)
            ->firstOrFail();

        try {
            $newData = $this->contentService->regenerateSlide(
                topic:       $content->topic,
                purpose:     $slide->purpose,
                contentType: $content->content_type,
                tone:        $content->tone,
                layout:      $slide->layout
            );

            // layout & purpose di-guarantee oleh service (server-side override)
            // sehingga aman disimpan langsung dari $newData
            $slide->update([
                'headline'    => $newData['headline'] ?? $slide->headline,
                'body'        => $newData['body']     ?? $slide->body,
                'layout'      => $newData['layout']   ?? $slide->layout,
                'purpose'     => $newData['purpose']  ?? $slide->purpose,
                'design_data' => array_merge($slide->design_data ?? [], [
                    'image_prompt' => $newData['image_prompt'] ?? '',
                ]),
            ]);

            return response()->json(['success' => true, 'slide' => $slide->fresh()]);

        } catch (\Exception $e) {
            Log::error('InstagramContentController@regenerateSlide: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    // =========================================================================
    // Render slide canvas partial (HTML only, untuk JS swap setelah regen)
    // =========================================================================

    public function renderSlideCanvas(int $contentId, int $slideId)
    {
        $content = InstagramContent::with('slides')->findOrFail($contentId);
        $slide   = InstagramContentSlide::where('id', $slideId)
                    ->where('instagram_content_id', $contentId)
                    ->firstOrFail();
        $brand   = $content->brand_snapshot ?? InstagramBrandResolver::resolve();

        $html = view('instagram_studio.partials.slide_canvas', compact('slide', 'content', 'brand'))->render();

        return response()->json(['success' => true, 'html' => $html]);
    }

    // =========================================================================
    // Regenerate caption only
    // =========================================================================

    public function regenerateCaption(int $id)
    {
        $content = InstagramContent::with('slides')->findOrFail($id);

        try {
            $result = $this->contentService->regenerateCaption(
                $content->topic,
                $content->slides->toArray()
            );

            $content->update([
                'caption'  => $result['caption'],
                'hashtags' => $result['hashtags'],
            ]);

            return response()->json(['success' => true, 'caption' => $result['caption'], 'hashtags' => $result['hashtags']]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    // =========================================================================
    // Add / Remove slide
    // =========================================================================

    public function addSlide(Request $request, int $id)
    {
        $content = InstagramContent::with('slides')->findOrFail($id);
        $maxSlides = 5;

        if ($content->slides->count() >= $maxSlides) {
            return response()->json(['success' => false, 'error' => 'Maksimal 5 slide.'], 422);
        }

        $nextNumber = ($content->slides->max('slide_number') ?? 0) + 1;
        $purposes   = ['hook', 'problem', 'solution', 'tips', 'cta'];
        $purpose    = $purposes[min($nextNumber - 1, 4)];

        $slide = InstagramContentSlide::create([
            'instagram_content_id' => $content->id,
            'slide_number'         => $nextNumber,
            'purpose'              => $purpose,
            'layout'               => $content->template_style === 'auto' ? 'hero' : $content->template_style,
            'headline'             => 'Judul Slide ' . $nextNumber,
            'body'                 => 'Isi konten slide ' . $nextNumber . '...',
        ]);

        $content->update(['slide_count' => $content->slides()->count()]);

        return response()->json(['success' => true, 'slide' => $slide]);
    }

    public function removeSlide(int $contentId, int $slideId)
    {
        $content = InstagramContent::with('slides')->findOrFail($contentId);

        if ($content->slides->count() <= 1) {
            return response()->json(['success' => false, 'error' => 'Minimal harus ada 1 slide.'], 422);
        }

        $slide = InstagramContentSlide::where('id', $slideId)
            ->where('instagram_content_id', $contentId)
            ->firstOrFail();

        // Delete rendered file if exists
        if ($slide->rendered_path) {
            Storage::disk('public')->delete($slide->rendered_path);
        }

        $slide->delete();

        // Re-number remaining slides
        $content->slides()->orderBy('slide_number')->get()->each(function ($s, $i) {
            $s->update(['slide_number' => $i + 1]);
        });

        $content->update(['slide_count' => $content->slides()->count()]);

        return response()->json(['success' => true]);
    }

    // =========================================================================
    // Reorder slides
    // =========================================================================

    public function reorderSlides(Request $request, int $id)
    {
        $request->validate(['order' => 'required|array']);

        $content = InstagramContent::findOrFail($id);

        foreach ($request->order as $position => $slideId) {
            InstagramContentSlide::where('id', $slideId)
                ->where('instagram_content_id', $content->id)
                ->update(['slide_number' => $position + 1]);
        }

        return response()->json(['success' => true]);
    }

    // =========================================================================
    // Upload image for slide
    // =========================================================================

    public function uploadImage(Request $request, int $contentId, int $slideId)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $slide = InstagramContentSlide::where('id', $slideId)
            ->where('instagram_content_id', $contentId)
            ->firstOrFail();

        // Delete old image if exists
        if ($slide->image_path) {
            Storage::disk('public')->delete($slide->image_path);
        }

        $path = $request->file('image')->store('instagram/uploads', 'public');
        $slide->update(['image_path' => $path]);

        return response()->json([
            'success'   => true,
            'image_url' => asset('storage/' . $path),
        ]);
    }

    // =========================================================================
    // Save as draft / change status
    // =========================================================================

    public function saveDraft(int $id)
    {
        $content = InstagramContent::findOrFail($id);
        $content->update(['status' => 'draft']);

        return response()->json(['success' => true]);
    }

    public function submitReview(int $id)
    {
        $content = InstagramContent::findOrFail($id);
        $content->update(['status' => 'review']);

        return redirect()->route('instagram-studio.preview', $id)
            ->with('success', 'Konten dikirim untuk review. Menunggu persetujuan.');
    }

    public function approve(int $id)
    {
        $content = InstagramContent::findOrFail($id);
        $content->update([
            'status'      => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    // =========================================================================
    // Publish (stub — Phase 3)
    // =========================================================================

    public function publish(Request $request, int $id)
    {
        $content = InstagramContent::with('slides')->findOrFail($id);

        if ($content->status !== 'approved') {
            return response()->json([
                'success' => false,
                'error'   => 'Konten harus disetujui terlebih dahulu sebelum dipublish.',
            ], 422);
        }

        // Kumpulkan URL publik gambar tiap slide (harus accessible dari internet)
        $imageUrls = $content->slides
            ->sortBy('slide_number')
            ->filter(fn($s) => !empty($s->image_path))
            ->map(fn($s) => $s->image_url)          // accessor: asset('storage/...')
            ->filter(fn($u) => !empty($u))
            ->values()
            ->toArray();

        $caption = trim(($content->caption ?? '') . "

" . implode(' ', $content->hashtags ?? []));

        $result = $this->publisher->publish($imageUrls, $caption);

        $publication = InstagramPublication::create([
            'instagram_content_id' => $content->id,
            'platform'             => 'instagram',
            'status'               => $result['success'] ? 'published' : 'failed',
            'external_post_id'     => $result['external_post_id'] ?? null,
            'error_message'        => $result['error_message'] ?? null,
            'published_at'         => $result['success'] ? now() : null,
        ]);

        if ($result['success']) {
            $content->update(['status' => 'published']);
        } else {
            $content->update(['publish_error' => $result['error_message']]);
        }

        return response()->json([
            'success' => $result['success'],
            'message' => $result['error_message'] ?? 'Dipublikasikan.',
        ]);
    }

    // =========================================================================
    // Refresh Instagram access token
    // =========================================================================

    public function refreshToken()
    {
        $result = $this->publisher->refreshToken();

        if ($result['success']) {
            // Tampilkan token baru ke admin — admin harus update .env manual
            // atau bisa auto-update .env jika diperlukan
            return response()->json([
                'success'      => true,
                'access_token' => $result['access_token'],
                'expires_in'   => $result['expires_in'],
                'message'      => 'Token berhasil diperbarui. Salin token baru ke .env INSTAGRAM_ACCESS_TOKEN',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['error_message'],
        ], 422);
    }

    // =========================================================================
    // Generate images (AI image generation per slide)
    // =========================================================================

    /** Generate images for ALL slides in a content */
    public function generateImages(int $id)
    {
        $content = InstagramContent::with('slides')->findOrFail($id);
        $results = $this->imageGenerator->generateAllSlides($content);

        $count = count($results);
        return response()->json([
            'success'   => true,
            'generated' => $count,
            'message'   => "{$count} gambar berhasil digenerate",
            'slides'    => collect($results)->map(fn($path) => Storage::disk('public')->url($path))->toArray(),
        ]);
    }

    /** Generate image for ONE slide */
    public function generateSlideImage(int $contentId, int $slideId)
    {
        $slide  = InstagramContentSlide::where('instagram_content_id', $contentId)->findOrFail($slideId);
        $prompt = $slide->design_data['image_prompt'] ?? null;

        if (!$prompt) {
            return response()->json(['success' => false, 'message' => 'Slide tidak memiliki image_prompt'], 422);
        }

        $path = $this->imageGenerator->generateForSlide($contentId, $slide->slide_order, $prompt);

        if (!$path) {
            return response()->json(['success' => false, 'message' => 'Gagal generate gambar'], 500);
        }

        $slide->update(['image_path' => $path]);
        $url = Storage::disk('public')->url($path);

        return response()->json([
            'success'   => true,
            'image_url' => $url,
            'message'   => 'Gambar berhasil digenerate',
        ]);
    }

    // =========================================================================
    // Destroy
    // =========================================================================

    public function destroy(int $id)
    {
        $content = InstagramContent::with('slides')->findOrFail($id);

        foreach ($content->slides as $slide) {
            if ($slide->rendered_path) Storage::disk('public')->delete($slide->rendered_path);
            if ($slide->image_path)    Storage::disk('public')->delete($slide->image_path);
        }

        $content->delete();

        return redirect()->route('instagram-studio.index')
            ->with('success', 'Konten dihapus.');
    }
}
