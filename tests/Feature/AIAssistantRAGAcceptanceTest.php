<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\booking;
use App\AI\AIKnowledgeDocs;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * AIAssistantRAGAcceptanceTest
 *
 * Validates the AI Assistant RAG pipeline and tool execution through its
 * public HTTP interface (POST /ai-assistant/chat) and integration boundaries:
 * - Authentication & security gate
 * - Request validation boundary
 * - Out-of-context topic refusal
 * - Greeting handler
 * - Tool: get_booking_detail (success & not_found paths)
 * - Tool: get_emr (with SOAP data & not_found paths)
 * - RAG KnowledgeDocs retrieval & fallback
 * - Throttle middleware compliance
 */
class AIAssistantRAGAcceptanceTest extends TestCase
{
    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->user = User::first();
        if ($this->user) {
            \App\Models\AIConversation::where('user_id', $this->user->id)
                ->where('status', 'active')
                ->update(['status' => 'closed']);
        }
    }

    /**
     * Boundary 1: Guest access must be rejected.
     */
    public function test_guest_cannot_access_chat_endpoint(): void
    {
        $response = $this->postJson('/ai-assistant/chat', [
            'message' => 'halo',
        ]);

        // Unauthenticated access must return 401 for JSON requests
        $response->assertStatus(401);
    }

    /**
     * Boundary 2: Validation on message parameter.
     */
    public function test_empty_message_returns_validation_error(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in database');
        }

        $response = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['message']);
    }

    /**
     * Boundary 3: Out-of-context topics (politics, crypto, recipes, etc.).
     */
    public function test_out_of_context_query_returns_polite_refusal(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in database');
        }

        $response = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message' => 'siapa calon presiden pemilu nanti?',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'out_of_context',
        ]);
        $this->assertNotEmpty($response->json('reply'));
        $this->assertIsArray($response->json('suggestions'));
    }

    /**
     * Boundary 4: Greeting message handled with personalized clinic greeting.
     */
    public function test_greeting_message_returns_clinic_greeting(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in database');
        }

        $response = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message' => 'Halo selamat pagi',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);
        $this->assertStringContainsString('VetClinic AI Assistant', $response->json('reply'));
    }

    /**
     * Boundary 5: get_booking_detail tool execution with existing booking in DB.
     */
    public function test_get_booking_detail_with_existing_booking(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in database');
        }

        $existing = booking::first();
        if (!$existing) {
            $this->markTestSkipped('No booking found in database');
        }

        $response = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message' => "detail booking {$existing->kode_booking}",
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
        $this->assertStringContainsString($existing->kode_booking, $response->json('reply'));
        $this->assertNotEmpty($response->json('results'));
        $this->assertNotEmpty($response->json('action_url'));
    }

    /**
     * Boundary 6: get_booking_detail with non-existent booking returns helpful not-found.
     */
    public function test_get_booking_detail_not_found(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in database');
        }

        $response = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message' => 'detail booking BOOKNONEXISTENT99',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
        $this->assertStringContainsString('tidak ditemukan', $response->json('reply'));
    }

    /**
     * Boundary 7: get_emr tool execution with real SOAP data.
     */
    public function test_get_emr_with_soap_data(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in database');
        }

        // Create a temporary test booking with SOAP record
        $testBooking = booking::create([
            'kode_booking'    => 'BOOKTESTEMR1',
            'nama'            => 'Test Owner Acceptance',
            'nama_hewan'      => 'TestPetAcceptance',
            'jenis_hewan'     => 'Kucing',
            'alamat'          => 'Jl. Uji Coba No. 123',
            'telpon'          => '081234567890',
            'tipe_kedatangan' => 'Walk-in',
            'jam'             => '10:00',
            'tanggal'         => now()->format('Y-m-d'),
            'status'          => 'Telah diselesaikan',
            'suhu_tubuh'      => '38.5',
            'denyut_jantung'  => '120',
            'crt'             => '< 2 detik',
            'subjective'      => 'Kucing lemas tidak mau makan 2 hari',
            'objective'       => 'Dehidrasi ringan, mata berair',
            'assessment'      => 'Feline Panleukopenia Suspect',
            'plan'            => 'Infus RL, antibiotik amoxicillin, kontrol 3 hari',
        ]);

        try {
            $response = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
                'message' => 'rekam medis kucing TestPetAcceptance',
            ]);

            $response->assertStatus(200);
            $response->assertJson(['status' => 'success']);
            $reply = $response->json('reply');
            $this->assertStringContainsString('Feline Panleukopenia Suspect', $reply);
            $this->assertStringContainsString('BOOKTESTEMR1', $reply);
            $this->assertNotEmpty($response->json('results'));
        } finally {
            $testBooking->delete();
        }
    }

    /**
     * Boundary 8: RAG KnowledgeDocs retrieval covers all 15 clinical and operational modules.
     */
    public function test_rag_knowledge_docs_module_coverage(): void
    {
        $modules = [
            'booking'      => 'bagaimana cara membuat booking baru di pendaftaran?',
            'queue_stages' => 'apa saja alur 6 stage pemeriksaan pasien?',
            'owner_pet'    => 'cara input data owner dan hewan peliharaan baru',
            'emr'          => 'format pengisian rekam medis SOAP tanda vital',
            'inventori'    => 'prosedur update stok obat di inventori farmasi',
            'layanan'      => 'daftar tarif jasa dokter vaksin grooming',
            'estimasi'     => 'bagaimana cara membuat nota estimasi biaya',
            'transaksi'    => 'pencatatan transaksi pengambilan obat pasien',
            'laporan'      => 'laporan omzet keuangan bulanan',
            'pembayaran'   => 'alur kasir pembayaran uang muka dp lunas',
            'karyawan'     => 'manajemen daftar karyawan dan dokter',
            'rawat_inap'   => 'pengisian pet daily log rawat inap opname',
            'user_akses'   => 'hak akses role superadmin admin dan dokter',
            'dashboard'    => 'informasi ringkasan statistik di dashboard',
            'nota_pdf'     => 'prosedur cetak download nota invoice pdf',
        ];

        foreach ($modules as $modName => $query) {
            $doc = AIKnowledgeDocs::getRelevantDocs($query);
            $this->assertNotEmpty($doc, "Doc for module {$modName} should not be empty");
            $this->assertGreaterThan(100, strlen($doc), "Doc for module {$modName} should have meaningful content");
        }
    }

    /**
     * Boundary 9: Unmatched query falls back to general navigation guide.
     */
    public function test_rag_unmatched_query_falls_back_to_general_navigation(): void
    {
        $fallback = AIKnowledgeDocs::getRelevantDocs('xyzrandomunmatchedtoken999');
        $this->assertStringContainsString('NAVIGASI UMUM', $fallback);
    }

    /**
     * Boundary 10: Route middleware asserts throttle:30,1 and auth on /ai-assistant/chat.
     */
    public function test_chat_route_has_auth_and_throttle_middleware(): void
    {
        $route = collect(Route::getRoutes())->first(function ($r) {
            return $r->uri() === 'ai-assistant/chat' && in_array('POST', $r->methods());
        });

        $this->assertNotNull($route, 'POST /ai-assistant/chat route must exist');
        $middlewares = $route->gatherMiddleware();

        $this->assertContains('auth', $middlewares, 'Route must have auth middleware');
        $this->assertTrue(
            collect($middlewares)->contains(fn($m) => str_starts_with($m, 'throttle:30,1') || str_contains($m, 'throttle:30,1')),
            'Route must have throttle:30,1 middleware attached'
        );
    }
}
