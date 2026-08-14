<?php

namespace Tests\Feature\LabResults;

use App\Models\LabOrderItem;
use App\Models\LabResult;
use Database\Seeders\LabResultsScenarioSeeder;
use Tests\TestCase;

class ResultEntryTest extends TestCase
{
    use InteractsWithLabResults;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedScenario();
    }

    public function test_technician_captures_a_numeric_result(): void
    {
        $itemId = $this->pendingItemId(LabResultsScenarioSeeder::TENANT_LAB_ID);
        $sampleId = $this->sampleId(LabResultsScenarioSeeder::TENANT_LAB_ID);

        $this->withHeaders($this->labTechHeaders())
            ->postJson('/api/v1/lab-results', [
                'lab_order_item_id' => $itemId,
                'sample_id' => $sampleId,
                'numeric_value' => '95',
            ])
            ->assertCreated()
            ->assertJsonPath('data.numeric_value', '95.0000');

        $this->assertDatabaseHas('lab_results', [
            'tenant_id' => LabResultsScenarioSeeder::TENANT_LAB_ID,
            'lab_order_item_id' => $itemId,
            'sample_id' => $sampleId,
            'is_abnormal' => false,
            'is_critical' => false,
        ]);

        $this->assertDatabaseHas('lab_order_items', [
            'id' => $itemId,
            'status' => 'resultado_listo',
        ]);
    }

    public function test_entry_rejects_item_without_value(): void
    {
        $itemId = $this->pendingItemId(LabResultsScenarioSeeder::TENANT_LAB_ID);
        $sampleId = $this->sampleId(LabResultsScenarioSeeder::TENANT_LAB_ID);

        $this->withHeaders($this->labTechHeaders())
            ->postJson('/api/v1/lab-results', [
                'lab_order_item_id' => $itemId,
                'sample_id' => $sampleId,
            ])
            ->assertUnprocessable();
    }

    public function test_entry_rejects_nonexistent_item(): void
    {
        $sampleId = $this->sampleId(LabResultsScenarioSeeder::TENANT_LAB_ID);

        $this->withHeaders($this->labTechHeaders())
            ->postJson('/api/v1/lab-results', [
                'lab_order_item_id' => 99999,
                'sample_id' => $sampleId,
                'numeric_value' => '95',
            ])
            ->assertStatus(409);
    }

    public function test_duplicate_capture_is_rejected(): void
    {
        $itemId = $this->pendingItemId(LabResultsScenarioSeeder::TENANT_LAB_ID);
        $sampleId = $this->sampleId(LabResultsScenarioSeeder::TENANT_LAB_ID);
        $headers = $this->labTechHeaders();

        $payload = [
            'lab_order_item_id' => $itemId,
            'sample_id' => $sampleId,
            'numeric_value' => '95',
        ];

        $this->withHeaders($headers)->postJson('/api/v1/lab-results', $payload)->assertCreated();

        $this->withHeaders($headers)->postJson('/api/v1/lab-results', $payload)->assertStatus(409);

        $this->assertSame(1, LabResult::query()->where('lab_order_item_id', $itemId)->count());
    }

    public function test_recepcionista_cannot_capture_results(): void
    {
        $itemId = $this->pendingItemId(LabResultsScenarioSeeder::TENANT_LAB_ID);
        $sampleId = $this->sampleId(LabResultsScenarioSeeder::TENANT_LAB_ID);

        $this->withHeaders($this->recepcionistaHeaders())
            ->postJson('/api/v1/lab-results', [
                'lab_order_item_id' => $itemId,
                'sample_id' => $sampleId,
                'numeric_value' => '95',
            ])
            ->assertForbidden();
    }
}