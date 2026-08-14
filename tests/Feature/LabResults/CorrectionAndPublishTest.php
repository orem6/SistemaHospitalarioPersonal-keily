<?php

namespace Tests\Feature\LabResults;

use App\Models\LabResult;
use App\Models\LabResultCorrection;
use Database\Seeders\LabResultsScenarioSeeder;
use Tests\TestCase;

class CorrectionAndPublishTest extends TestCase
{
    use InteractsWithLabResults;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedScenario();
    }

    /**
     * @return array{result: int, headers: array<string, string>}
     */
    private function capturedResult(): array
    {
        $headers = $this->labTechHeaders();
        $itemId = $this->pendingItemId(LabResultsScenarioSeeder::TENANT_LAB_ID);
        $sampleId = $this->sampleId(LabResultsScenarioSeeder::TENANT_LAB_ID);

        $response = $this->withHeaders($headers)
            ->postJson('/api/v1/lab-results', [
                'lab_order_item_id' => $itemId,
                'sample_id' => $sampleId,
                'numeric_value' => '95',
            ])->assertCreated();

        return ['result' => $response->json('data.id'), 'headers' => $headers];
    }

    public function test_technician_corrects_pending_result_and_trail_is_recorded(): void
    {
        $captured = $this->capturedResult();
        $resultId = $captured['result'];

        $this->withHeaders($captured['headers'])
            ->patchJson("/api/v1/lab-results/{$resultId}/correct", [
                'numeric_value' => '85',
                'reason' => 'Redigitación del valor.',
            ])
            ->assertOk()
            ->assertJsonPath('data.numeric_value', '85.0000');

        $this->assertSame(85.0, (float) LabResult::query()->findOrFail($resultId)->numeric_value);

        $correction = LabResultCorrection::query()
            ->where('lab_result_id', $resultId)
            ->where('field', 'numeric_value')
            ->firstOrFail();

        $this->assertSame('95.0000', $correction->previous_value);
        $this->assertSame('85', $correction->new_value);
        $this->assertSame('Redigitación del valor.', $correction->reason);
    }

    public function test_technician_publishes_validated_result(): void
    {
        $captured = $this->capturedResult();
        $resultId = $captured['result'];

        $response = $this->withHeaders($captured['headers'])
            ->postJson("/api/v1/lab-results/{$resultId}/publish")
            ->assertOk()
            ->assertJsonPath('data.published_by', $this->labTechUserId());

        $this->assertNotNull($response->json('data.published_at'));
        $this->assertNotNull(LabResult::query()->findOrFail($resultId)->published_at);
    }

    public function test_correcting_published_result_is_rejected(): void
    {
        $captured = $this->capturedResult();
        $resultId = $captured['result'];
        $headers = $captured['headers'];

        $this->withHeaders($headers)->postJson("/api/v1/lab-results/{$resultId}/publish")->assertOk();

        $this->withHeaders($headers)
            ->patchJson("/api/v1/lab-results/{$resultId}/correct", [
                'numeric_value' => '80',
                'reason' => 'Intento tardío.',
            ])
            ->assertStatus(409);
    }

    public function test_republishing_is_rejected(): void
    {
        $captured = $this->capturedResult();
        $resultId = $captured['result'];
        $headers = $captured['headers'];

        $this->withHeaders($headers)->postJson("/api/v1/lab-results/{$resultId}/publish")->assertOk();
        $this->withHeaders($headers)->postJson("/api/v1/lab-results/{$resultId}/publish")->assertStatus(409);
    }

    public function test_correcting_without_changes_is_rejected(): void
    {
        $captured = $this->capturedResult();
        $resultId = $captured['result'];

        $this->withHeaders($captured['headers'])
            ->patchJson("/api/v1/lab-results/{$resultId}/correct", [
                'numeric_value' => '95',
                'reason' => 'Sin cambios.',
            ])
            ->assertUnprocessable();
    }

    public function test_correcting_nonexistent_result_is_404(): void
    {
        $this->withHeaders($this->labTechHeaders())
            ->patchJson('/api/v1/lab-results/99999/correct', [
                'numeric_value' => '10',
                'reason' => 'Prueba.',
            ])
            ->assertNotFound();
    }

    private function labTechUserId(): int
    {
        return (int) \App\Models\User::query()->where('email', 'lab.resultados@demo.local')->value('id');
    }
}