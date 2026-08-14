<?php

namespace Tests\Feature\LabResults;

use App\Models\LabOrderItem;
use App\Models\Sample;
use App\Models\User;
use Database\Seeders\LabResultsScenarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

trait InteractsWithLabResults
{
    use RefreshDatabase;

    protected function seedScenario(): void
    {
        $this->seed(LabResultsScenarioSeeder::class);
    }

    protected function apiHeaders(string $email, string $tenantId): array
    {
        /** @var User $user */
        $user = User::query()->where('email', $email)->firstOrFail();
        $token = auth('api')->login($user);

        return [
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$token}",
            'X-Tenant-ID' => $tenantId,
        ];
    }

    protected function labTechHeaders(): array
    {
        return $this->apiHeaders('lab.resultados@demo.local', LabResultsScenarioSeeder::TENANT_LAB_ID);
    }

    protected function medicoHeaders(): array
    {
        return $this->apiHeaders('medico.resultados@demo.local', LabResultsScenarioSeeder::TENANT_LAB_ID);
    }

    protected function medicoOtherTenantHeaders(): array
    {
        return $this->apiHeaders('medico.otro@demo.local', LabResultsScenarioSeeder::TENANT_OTHER_ID);
    }

    protected function recepcionistaHeaders(): array
    {
        return $this->apiHeaders('recepcionista@demo.local', LabResultsScenarioSeeder::TENANT_LAB_ID);
    }

    protected function pendingItemId(string $tenantId): int
    {
        return LabOrderItem::query()
            ->whereHas('labOrder', fn ($query) => $query->where('tenant_id', $tenantId))
            ->where('status', 'pendiente')
            ->firstOrFail()->id;
    }

    protected function sampleId(string $tenantId): int
    {
        return Sample::query()->where('tenant_id', $tenantId)->firstOrFail()->id;
    }
}