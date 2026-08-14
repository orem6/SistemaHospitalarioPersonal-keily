<?php

namespace Tests\Feature\LabResults;

use Database\Seeders\LabResultsScenarioSeeder;
use Tests\TestCase;

class PendingConsultationTest extends TestCase
{
    use InteractsWithLabResults;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedScenario();
    }

    public function test_technician_views_only_pending_results_of_its_tenant(): void
    {
        $tenantId = LabResultsScenarioSeeder::TENANT_LAB_ID;
        $itemId = $this->pendingItemId($tenantId);

        $response = $this->withHeaders($this->labTechHeaders())
            ->getJson('/api/v1/lab-results/pending');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.labOrderItemId', $itemId)
            ->assertJsonPath('data.0.orderCode', 'LAB-DEMO-0001')
            ->assertJsonPath('data.0.testName', 'Glucosa');

        $response->assertJsonMissing(['orderCode' => 'LAB-DEMO-0002']);
    }

    public function test_pending_results_are_isolated_by_tenant(): void
    {
        $otherTenantId = LabResultsScenarioSeeder::TENANT_OTHER_ID;

        $response = $this->withHeaders($this->apiHeaders(
            'lab.otro@demo.local',
            $otherTenantId
        ))->getJson('/api/v1/lab-results/pending');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.orderCode', 'LAB-DEMO-0002');
    }

    public function test_physician_cannot_list_pending_results(): void
    {
        $this->withHeaders($this->medicoHeaders())
            ->getJson('/api/v1/lab-results/pending')
            ->assertForbidden();
    }
}