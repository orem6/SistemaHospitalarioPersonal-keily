<?php

namespace Tests\Feature\LabResults;

use Database\Seeders\LabResultsScenarioSeeder;
use Tests\TestCase;

class PublishedConsultationTest extends TestCase
{
    use InteractsWithLabResults;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedScenario();
    }

    /**
     * @return array{result: int}
     */
    private function publishedResult(): array
    {
        $headers = $this->labTechHeaders();
        $itemId = $this->pendingItemId(LabResultsScenarioSeeder::TENANT_LAB_ID);
        $sampleId = $this->sampleId(LabResultsScenarioSeeder::TENANT_LAB_ID);

        $created = $this->withHeaders($headers)->postJson('/api/v1/lab-results', [
            'lab_order_item_id' => $itemId,
            'sample_id' => $sampleId,
            'numeric_value' => '95',
        ])->assertCreated();

        $resultId = $created->json('data.id');

        $this->withHeaders($headers)->postJson("/api/v1/lab-results/{$resultId}/publish")->assertOk();

        return ['result' => $resultId];
    }

    public function test_physician_reads_a_published_result(): void
    {
        $resultId = $this->publishedResult()['result'];

        $this->withHeaders($this->medicoHeaders())
            ->getJson("/api/v1/lab-results/{$resultId}")
            ->assertOk()
            ->assertJsonPath('data.labResultId', $resultId)
            ->assertJsonPath('data.testName', 'Glucosa')
            ->assertJsonPath('data.numericValue', '95.0000');
    }

    public function test_physician_cannot_read_an_unpublished_result(): void
    {
        $headers = $this->labTechHeaders();
        $itemId = $this->pendingItemId(LabResultsScenarioSeeder::TENANT_LAB_ID);
        $sampleId = $this->sampleId(LabResultsScenarioSeeder::TENANT_LAB_ID);

        $created = $this->withHeaders($headers)->postJson('/api/v1/lab-results', [
            'lab_order_item_id' => $itemId,
            'sample_id' => $sampleId,
            'numeric_value' => '95',
        ])->assertCreated();

        $this->withHeaders($this->medicoHeaders())
            ->getJson("/api/v1/lab-results/{$created->json('data.id')}")
            ->assertNotFound();
    }

    public function test_cross_tenant_read_is_rejected(): void
    {
        $resultId = $this->publishedResult()['result'];

        $this->withHeaders($this->medicoOtherTenantHeaders())
            ->getJson("/api/v1/lab-results/{$resultId}")
            ->assertNotFound();
    }

    public function test_technician_cannot_read_published_endpoint(): void
    {
        $resultId = $this->publishedResult()['result'];

        $this->withHeaders($this->labTechHeaders())
            ->getJson("/api/v1/lab-results/{$resultId}")
            ->assertForbidden();
    }
}