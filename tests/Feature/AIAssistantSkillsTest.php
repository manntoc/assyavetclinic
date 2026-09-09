<?php

namespace Tests\Feature;

use App\AI\AISkillManager;
use App\AI\AIStateMachine;
use App\Models\AIConversation;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\CreatesApplication;

class AIAssistantSkillsTest extends BaseTestCase
{
    use CreatesApplication;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        $this->user = User::first();
        if ($this->user) {
            AIConversation::where('user_id', $this->user->id)
                ->where('status', 'active')
                ->update(['status' => 'closed']);
        }
    }

    /**
     * Test 1: /help or /skills command returns full list of modular skills and quick chips.
     */
    public function test_slash_help_command_returns_available_skills_and_quick_chips(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in database');
        }

        $response = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message' => '/help',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
        $this->assertStringContainsString('Modul &amp; Skill AI Agent Assyavet', $response->json('reply'));
        $this->assertStringContainsString('/karyawan', $response->json('reply'));
        $this->assertStringContainsString('/inventori', $response->json('reply'));
        $this->assertStringContainsString('/laporan', $response->json('reply'));

        $suggestions = $response->json('suggestions');
        $this->assertIsArray($suggestions);
        $this->assertContains('/karyawan', $suggestions);
        $this->assertContains('/owner', $suggestions);
        $this->assertContains('/inventori', $suggestions);
    }

    /**
     * Test 2: /karyawan command activates karyawan specialist mode.
     */
    public function test_slash_karyawan_switches_active_skill_to_karyawan(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in database');
        }

        $response = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message' => '/karyawan',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'       => 'success',
            'active_skill' => 'karyawan',
            'skill_title'  => 'Dokter & Karyawan Specialist',
        ]);
        $this->assertStringContainsString('Mode <strong>Dokter & Karyawan Specialist</strong> aktif', $response->json('reply'));
    }

    /**
     * Test 3: /inventori activates inventory pharmacy specialist mode.
     */
    public function test_slash_inventori_switches_active_skill_to_inventori(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in database');
        }

        $response = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message' => '/inventori',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'       => 'success',
            'active_skill' => 'inventori',
            'skill_title'  => 'Inventori & Apotek Specialist',
        ]);
        $this->assertStringContainsString('Mode <strong>Inventori & Apotek Specialist</strong> aktif', $response->json('reply'));
    }

    /**
     * Test 4: /owner activates owner and pet specialist mode.
     */
    public function test_slash_owner_switches_active_skill_to_owner(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in database');
        }

        $response = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message' => '/owner',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'       => 'success',
            'active_skill' => 'owner',
            'skill_title'  => 'Owner & Pasien Specialist',
        ]);
    }

    /**
     * Test 5: /booking activates booking and appointment specialist mode.
     */
    public function test_slash_booking_switches_active_skill_to_booking(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in database');
        }

        $response = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message' => '/booking',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'       => 'success',
            'active_skill' => 'booking',
            'skill_title'  => 'Booking & Antrean Specialist',
        ]);
    }

    /**
     * Test 6: /emr activates medical record SOAP specialist mode.
     */
    public function test_slash_emr_switches_active_skill_to_emr(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in database');
        }

        $response = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message' => '/emr',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'       => 'success',
            'active_skill' => 'emr',
            'skill_title'  => 'Rekam Medis & SOAP Specialist',
        ]);
    }

    /**
     * Test 7: /laporan activates finance and omzet specialist mode.
     */
    public function test_slash_laporan_switches_active_skill_to_laporan(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in database');
        }

        $response = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message' => '/laporan',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'       => 'success',
            'active_skill' => 'laporan',
            'skill_title'  => 'Keuangan & Omzet Specialist',
        ]);
    }

    /**
     * Test 8: /layanan activates service pricing and clinical catalog specialist mode.
     */
    public function test_slash_layanan_switches_active_skill_to_layanan(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in database');
        }

        $response = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message' => '/layanan',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'       => 'success',
            'active_skill' => 'layanan',
            'skill_title'  => 'Tarif Layanan Specialist',
        ]);
    }

    /**
     * Test 9: /general or /reset clears active skill back to general assistant mode.
     */
    public function test_slash_general_resets_active_skill_to_null(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in database');
        }

        // 1. Activate a skill
        $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message' => '/karyawan',
        ]);

        // 2. Reset back to general
        $response = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message' => '/general',
        ]);

        $response->assertStatus(200);
        $this->assertNull($response->json('active_skill'));
        $this->assertStringContainsString('Mode <strong>Asisten Umum</strong> aktif', $response->json('reply'));
    }

    /**
     * Test 10: Slash command with inline query switches mode and executes query immediately.
     */
    public function test_slash_command_with_inline_query_executes_in_specialist_mode(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in database');
        }

        $response = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message' => '/karyawan siapa yang jaga hari ini?',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'       => 'success',
            'active_skill' => 'karyawan',
        ]);
        // Should return staff duty results or schedule
        $reply = $response->json('reply');
        $this->assertTrue(
            str_contains($reply, 'Karyawan') ||
            str_contains($reply, 'Dokter') ||
            str_contains($reply, 'Staf') ||
            str_contains($reply, 'Jadwal')
        );
    }

    /**
     * Test 11: Specialist mode persists across subsequent turns in the same conversation.
     */
    public function test_specialist_mode_persists_across_subsequent_turns(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in database');
        }

        // Turn 1: Switch to /karyawan
        $turn1 = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message' => '/karyawan',
        ]);
        $turn1->assertStatus(200);
        $convId = $turn1->json('conversation_id');

        // Turn 2: Ask subsequent question without repeating /karyawan
        $turn2 = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message'         => 'jadwal hari ini',
            'conversation_id' => $convId,
        ]);

        $turn2->assertStatus(200);
        $this->assertEquals('karyawan', $turn2->json('active_skill'));
        $this->assertEquals('Dokter & Karyawan Specialist', $turn2->json('skill_title'));
    }

    /**
     * Test 12: Informational SOP queries in specialist mode route to weighted knowledge documentation.
     */
    public function test_specialist_karyawan_mode_routes_informational_sop_queries_to_rag_docs(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in database');
        }

        // Turn 1: Switch to /karyawan
        $turn1 = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message' => '/karyawan',
        ]);
        $convId = $turn1->json('conversation_id');

        // Turn 2: Ask how to add new staff (informational query)
        $turn2 = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message'         => 'bagaimana cara menambah karyawan baru di sistem?',
            'conversation_id' => $convId,
        ]);

        $turn2->assertStatus(200);
        $this->assertEquals('karyawan', $turn2->json('active_skill'));
        $reply = $turn2->json('reply');
        // Reply must contain SOP instructions for staff management or guidance
        $this->assertTrue(
            str_contains(strtolower($reply), 'karyawan') ||
            str_contains(strtolower($reply), 'menu') ||
            str_contains(strtolower($reply), 'role') ||
            str_contains(strtolower($reply), 'spesialis')
        );
    }
}
