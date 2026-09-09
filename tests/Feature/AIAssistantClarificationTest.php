<?php

namespace Tests\Feature;

use App\AI\AIStateMachine;
use App\Models\AIConversation;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\CreatesApplication;

class AIAssistantClarificationTest extends BaseTestCase
{
    use CreatesApplication;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->user = User::first();
        if ($this->user) {
            AIConversation::where('user_id', $this->user->id)
                ->where('status', 'active')
                ->update(['status' => 'closed']);
        }
    }

    /**
     * Test 1: Broad owner query returns clarification prompt instead of raw DB search.
     */
    public function test_broad_owner_query_requires_clarification(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in database');
        }

        $response = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message' => 'data customer owner dan hewan',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
        $this->assertEquals(AIStateMachine::COLLECTING_DATA, $response->json('state'));
        $this->assertStringContainsString('Cari by Nama', $response->json('reply'));
        $this->assertNotEmpty($response->json('suggestions'));
        $this->assertNotEmpty($response->json('action_url'));
    }

    /**
     * Test 2: Follow-up response resolves the pending clarification and executes search.
     */
    public function test_follow_up_answer_completes_clarified_owner_search(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in database');
        }

        // Turn 1: Broad query
        $turn1 = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message' => 'cari pemilik',
        ]);

        $turn1->assertStatus(200);
        $this->assertEquals(AIStateMachine::COLLECTING_DATA, $turn1->json('state'));
        $convId = $turn1->json('conversation_id');

        // Turn 2: Follow-up with list request
        $turn2 = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message'         => 'customer terbaru',
            'conversation_id' => $convId,
        ]);

        $turn2->assertStatus(200);
        $this->assertEquals(AIStateMachine::COMPLETED, $turn2->json('state'));
        $this->assertNotEmpty($turn2->json('results'));
    }

    /**
     * Test 3: Specific query with identifier bypasses clarification and executes immediately.
     */
    public function test_specific_owner_query_bypasses_clarification(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in database');
        }

        $response = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message' => 'pemilik kucing Koci',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
        // Must NOT be collecting data
        $this->assertNotEquals(AIStateMachine::COLLECTING_DATA, $response->json('state'));
        $this->assertNotEmpty($response->json('results'));
    }

    /**
     * Test 4: Broad booking query without code requires clarification.
     */
    public function test_broad_booking_query_requires_clarification(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in database');
        }

        $response = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message' => 'detail booking',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(AIStateMachine::COLLECTING_DATA, $response->json('state'));
        $this->assertStringContainsString('Kode Booking', $response->json('reply'));
    }

    /**
     * Test 5: Broad EMR query without patient/booking code requires clarification.
     */
    public function test_broad_emr_query_requires_clarification(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in database');
        }

        $response = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message' => 'rekam medis',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(AIStateMachine::COLLECTING_DATA, $response->json('state'));
        $this->assertStringContainsString('Nama Hewan', $response->json('reply'));
    }

    /**
     * Test 6: Broad inventory query without medicine name requires clarification.
     */
    public function test_broad_inventory_query_requires_clarification(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in database');
        }

        $response = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message' => 'stok obat',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(AIStateMachine::COLLECTING_DATA, $response->json('state'));
        $this->assertStringContainsString('nama obat', $response->json('reply'));
    }

    /**
     * Test 7: User can cancel during clarification, resetting state to IDLE.
     */
    public function test_cancel_during_clarification_resets_state_to_idle(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in database');
        }

        // Turn 1: Broad query
        $turn1 = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message' => 'cari owner',
        ]);
        $this->assertEquals(AIStateMachine::COLLECTING_DATA, $turn1->json('state'));
        $convId = $turn1->json('conversation_id');

        // Turn 2: User cancels
        $turn2 = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message'         => 'batal',
            'conversation_id' => $convId,
        ]);

        $this->assertEquals(AIStateMachine::IDLE, $turn2->json('state'));
        $this->assertStringContainsString('dibatalkan', $turn2->json('reply'));
    }

    /**
     * Test 8: Topic switch during clarification transitions smoothly to new intent.
     */
    public function test_topic_switch_during_clarification_transitions_smoothly(): void
    {
        if (!$this->user) {
            $this->markTestSkipped('No user found in database');
        }

        // Turn 1: Broad query puts state in COLLECTING_DATA
        $turn1 = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message' => 'cari owner',
        ]);
        $this->assertEquals(AIStateMachine::COLLECTING_DATA, $turn1->json('state'));
        $convId = $turn1->json('conversation_id');

        // Turn 2: User greets instead of answering
        $turn2 = $this->actingAs($this->user)->postJson('/ai-assistant/chat', [
            'message'         => 'halo selamat pagi',
            'conversation_id' => $convId,
        ]);

        $turn2->assertStatus(200);
        $this->assertEquals(AIStateMachine::IDLE, $turn2->json('state'));
        $this->assertStringContainsString('VetClinic AI Assistant', $turn2->json('reply'));
    }
}
