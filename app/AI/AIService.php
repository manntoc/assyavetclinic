<?php

namespace App\AI;

use App\AI\Contracts\AIProviderInterface;
use App\AI\Providers\NineRouterProvider;
use App\AI\Providers\GeminiProvider;
use App\AI\Providers\OpenAIProvider;
use App\AI\Tools\MasterData\SearchOwnerTool;
use App\AI\Tools\MasterData\SearchInventoryTool;
use App\AI\Tools\MasterData\GetStaffTool;
use App\AI\Tools\MasterData\SearchLayananTool;
use App\AI\Tools\Appointment\GetAppointmentsTool;
use App\AI\Tools\Appointment\GetTodayAppointmentsTool;
use App\AI\Tools\Appointment\CreateAppointmentTool;
use App\AI\Tools\Appointment\GetBookingDetailTool;
use App\AI\Tools\Appointment\GetEmrTool;
use App\AI\Tools\Report\GetOmzetReportTool;
use App\AI\Tools\Report\GetTransaksiLogTool;
use App\AI\Tools\Report\CompareOmzetTool;
use App\AI\Tools\TemporaryNota\GetTemporaryInvoiceTool;
use App\Models\User;

class AIService
{
    protected AIOrchestrator $orchestrator;
    protected AIToolRegistry $toolRegistry;

    public function __construct(
        NineRouterProvider $nineRouterProvider,
        AIContextManager $contextManager,
        AIToolRegistry $toolRegistry,
        AIToolExecutor $toolExecutor
    ) {
        $this->toolRegistry = $toolRegistry;

        // Register all tools
        $this->registerDefaultTools();

        // Build orchestrator
        $this->orchestrator = new AIOrchestrator(
            $nineRouterProvider,
            $contextManager,
            $this->toolRegistry,
            $toolExecutor
        );

        // Register fallback providers (used when 9Router is down)
        $this->orchestrator->setFallbackProviders(
            $this->buildFallbackProviders()
        );
    }

    /**
     * Register standard built-in tools.
     * GetAppointmentsTool (get_appointments) and GetTodayAppointmentsTool (get_today_appointments)
     * must have distinct getName() : fixed in their respective classes.
     */
    protected function registerDefaultTools(): void
    {
        // Master data
        $this->toolRegistry->register(new SearchOwnerTool());
        $this->toolRegistry->register(new SearchInventoryTool());
        $this->toolRegistry->register(new GetStaffTool());
        $this->toolRegistry->register(new SearchLayananTool());

        // Appointments
        $this->toolRegistry->register(new GetAppointmentsTool());       // name: get_appointments
        $this->toolRegistry->register(new GetTodayAppointmentsTool());  // name: get_today_appointments
        $this->toolRegistry->register(new CreateAppointmentTool());
        $this->toolRegistry->register(new GetBookingDetailTool());
        $this->toolRegistry->register(new GetEmrTool());

        // Reports
        $this->toolRegistry->register(new GetOmzetReportTool());
        $this->toolRegistry->register(new GetTransaksiLogTool());
        $this->toolRegistry->register(new CompareOmzetTool());

        // Invoicing
        $this->toolRegistry->register(new GetTemporaryInvoiceTool());
    }

    /**
     * Build ordered fallback provider list.
     * Providers with empty API keys return false on checkHealth() : safe to include.
     *
     * @return AIProviderInterface[]
     */
    protected function buildFallbackProviders(): array
    {
        $providers = [];

        // Gemini fallback
        try {
            $providers[] = new GeminiProvider();
        } catch (\Throwable $e) {
            // Provider not configured : skip
        }

        // OpenAI fallback
        try {
            $providers[] = new OpenAIProvider();
        } catch (\Throwable $e) {
            // Provider not configured : skip
        }

        return $providers;
    }

    /**
     * Process message from controller.
     */
    public function ask(string $message, ?User $user = null, ?int $conversationId = null): array
    {
        return $this->orchestrator->processMessage($message, $user, $conversationId);
    }
}
