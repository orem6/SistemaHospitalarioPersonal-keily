<?php

namespace Tests\Feature\LabResults;

use App\Models\LabResultVersion;
use App\Models\Sample;
use Database\Seeders\LabResultsScenarioSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pruebas del flujo versionado de la etapa 2 (Repository Pattern).
 * Rutas paralelas: /api/v1/lab-results/v2.
 */
final class VersionedResultsFlowTest extends TestCase
{
    use InteractsWithLabResults;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedScenario();
    }

    private function evidenciaTechHeaders(): array
    {
        return $this->apiHeaders(
            'lab.evidencias@demo.local',
            LabResultsScenarioSeeder::TENANT_EVIDENCIA_ID
        );
    }

    private function muestraEvidencia(string $barcode): Sample
    {
        return Sample::query()
            ->where('tenant_id', LabResultsScenarioSeeder::TENANT_EVIDENCIA_ID)
            ->where('barcode', $barcode)
            ->firstOrFail();
    }

    #[Test]
    public function tecnico_ve_solo_muestras_aceptadas_sin_resultado(): void
    {
        // Tenant principal: 1 aceptada sin resultado.
        $principal = $this->withHeaders($this->labTechHeaders())
            ->getJson('/api/v1/lab-results/v2/pendientes');
        $principal->assertOk()->assertJsonCount(1, 'data');
        $principal->assertJsonPath('data.0.barcode', 'BC-DEMO-000001');

        // Tenant de evidencias: solo la aceptada; la rechazada no aparece.
        $evidencia = $this->withHeaders($this->evidenciaTechHeaders())
            ->getJson('/api/v1/lab-results/v2/pendientes');
        $evidencia->assertOk()->assertJsonCount(1, 'data');
        $evidencia->assertJsonMissing(['barcode' => 'BC-EVD-000001']);
    }

    #[Test]
    public function muestra_rechazada_no_admite_resultados(): void
    {
        $sampleRechazada = $this->muestraEvidencia('BC-EVD-000001');

        $response = $this->withHeaders($this->evidenciaTechHeaders())
            ->postJson('/api/v1/lab-results/v2', [
                'sample_id' => $sampleRechazada->getKey(),
                'result_type' => 'NUMERICO',
                'numeric_value' => '150.0',
                'unit' => 'mg/dL',
            ]);

        $response->assertStatus(422)->assertJsonPath('error', 'MUESTRA_RECHAZADA');
        $this->assertSame(0, LabResultVersion::query()->count());
    }

    #[Test]
    public function ingreso_valida_tipo_y_unidad_contra_la_prueba(): void
    {
        $muestra = $this->muestraEvidencia('BC-EVD-000002');

        // La prueba es NUMÉRICA g/dL: un valor de texto debe rechazarse.
        $texto = $this->withHeaders($this->evidenciaTechHeaders())
            ->postJson('/api/v1/lab-results/v2', [
                'sample_id' => $muestra->getKey(),
                'result_type' => 'TEXTO',
                'text_value' => 'sin datos',
            ]);
        $texto->assertStatus(422)->assertJsonPath('error', 'TIPO_RESULTADO_INVALIDO');

        // Unidad distinta a la canónica de la prueba se rechaza.
        $unidad = $this->withHeaders($this->evidenciaTechHeaders())
            ->postJson('/api/v1/lab-results/v2', [
                'sample_id' => $muestra->getKey(),
                'result_type' => 'NUMERICO',
                'numeric_value' => '14.0',
                'unit' => 'mmol/L',
            ]);
        $unidad->assertStatus(422)->assertJsonPath('error', 'UNIDAD_INVALIDA');

        // Sin unidad también se rechaza (es obligatoria para numéricos).
        $sinUnidad = $this->withHeaders($this->evidenciaTechHeaders())
            ->postJson('/api/v1/lab-results/v2', [
                'sample_id' => $muestra->getKey(),
                'result_type' => 'NUMERICO',
                'numeric_value' => '14.0',
            ]);
        $sinUnidad->assertStatus(422)->assertJsonPath('error', 'UNIDAD_INVALIDA');

        $this->assertSame(0, LabResultVersion::query()->count());
    }

    #[Test]
    public function flujo_feliz_ingreso_correccion_conserva_versiones(): void
    {
        $muestra = $this->muestraEvidencia('BC-EVD-000002');

        // 1) Ingreso v1 correcto (con unidad canónica).
        $v1 = $this->withHeaders($this->evidenciaTechHeaders())
            ->postJson('/api/v1/lab-results/v2', [
                'sample_id' => $muestra->getKey(),
                'result_type' => 'NUMERICO',
                'numeric_value' => '13.5',
                'unit' => 'g/dL',
            ]);
        $v1->assertCreated()->assertJsonPath('data.version_number', 1);

        // Ya no aparece en pendientes.
        $pendientes = $this->withHeaders($this->evidenciaTechHeaders())
            ->getJson('/api/v1/lab-results/v2/pendientes');
        $pendientes->assertOk()->assertJsonCount(0, 'data');

        // Duplicado inicial rechazado por la política.
        $dup = $this->withHeaders($this->evidenciaTechHeaders())
            ->postJson('/api/v1/lab-results/v2', [
                'sample_id' => $muestra->getKey(),
                'result_type' => 'NUMERICO',
                'numeric_value' => '13.5',
                'unit' => 'g/dL',
            ]);
        $dup->assertStatus(409)->assertJsonPath('error', 'RESULTADO_YA_REGISTRADO');

        // 2) Corrección con motivo -> crea v2 y conserva v1.
        $v2 = $this->withHeaders($this->evidenciaTechHeaders())
            ->patchJson("/api/v1/lab-results/v2/muestras/{$muestra->getKey()}/correccion", [
                'motivo' => 'Error de transcripción del analizador ficticio.',
                'result_type' => 'NUMERICO',
                'numeric_value' => '15.1',
                'unit' => 'g/dL',
            ]);
        $v2->assertCreated();
        $v2->assertJsonPath('data.version_number', 2);
        $v2->assertJsonPath('data.corrige_a_version', 1);

        // 3) Historial conserva ambas versiones intactas.
        $historial = $this->withHeaders($this->evidenciaTechHeaders())
            ->getJson("/api/v1/lab-results/v2/historial/{$muestra->getKey()}");
        $historial->assertOk();
        $historial->assertJsonCount(2, 'data.versiones');
        $historial->assertJsonPath('data.vigente.version_number', 2);
        $historial->assertJsonPath('data.vigente.contenido.valor_numerico', 15.1);
        $historial->assertJsonPath('data.versiones.0.contenido.valor_numerico', 13.5); // v1 intacta

        // 4) Corrección idéntica se rechaza (SIN_CAMBIOS).
        $igual = $this->withHeaders($this->evidenciaTechHeaders())
            ->patchJson("/api/v1/lab-results/v2/muestras/{$muestra->getKey()}/correccion", [
                'motivo' => 'Intento de corrección sin cambios reales.',
                'result_type' => 'NUMERICO',
                'numeric_value' => '15.1',
                'unit' => 'g/dL',
            ]);
        $igual->assertStatus(422)->assertJsonPath('error', 'SIN_CAMBIOS');

        // 5) Motivo demasiado corto falla en la validación HTTP.
        $corto = $this->withHeaders($this->evidenciaTechHeaders())
            ->patchJson("/api/v1/lab-results/v2/muestras/{$muestra->getKey()}/correccion", [
                'motivo' => 'corto',
                'result_type' => 'NUMERICO',
                'numeric_value' => '16.0',
                'unit' => 'g/dL',
            ]);
        $corto->assertStatus(422)->assertJsonValidationErrors(['motivo']);

        // El historial sigue teniendo exactamente 2 versiones.
        $final = $this->withHeaders($this->evidenciaTechHeaders())
            ->getJson("/api/v1/lab-results/v2/historial/{$muestra->getKey()}");
        $final->assertOk()->assertJsonCount(2, 'data.versiones');
    }

    #[Test]
    public function correccion_requiere_version_previa(): void
    {
        $muestraPrincipal = Sample::query()
            ->where('tenant_id', LabResultsScenarioSeeder::TENANT_LAB_ID)
            ->where('barcode', 'BC-DEMO-000001')
            ->firstOrFail();

        $response = $this->withHeaders($this->labTechHeaders())
            ->patchJson("/api/v1/lab-results/v2/muestras/{$muestraPrincipal->getKey()}/correccion", [
                'motivo' => 'No existe versión previa que corregir.',
                'result_type' => 'NUMERICO',
                'numeric_value' => '99.0',
                'unit' => 'mg/dL',
            ]);

        $response->assertStatus(422)->assertJsonPath('error', 'RESULTADO_INEXISTENTE');
    }

    #[Test]
    public function historial_esta_isolado_por_tenant(): void
    {
        $muestra = $this->muestraEvidencia('BC-EVD-000002');

        // Se ingresa un resultado en el tenant de evidencias...
        $this->withHeaders($this->evidenciaTechHeaders())
            ->postJson('/api/v1/lab-results/v2', [
                'sample_id' => $muestra->getKey(),
                'result_type' => 'NUMERICO',
                'numeric_value' => '14.0',
                'unit' => 'g/dL',
            ])->assertCreated();

        // ...y el técnico de OTRO tenant no puede leer su historial.
        $intruso = $this->withHeaders($this->labTechHeaders())
            ->getJson("/api/v1/lab-results/v2/historial/{$muestra->getKey()}");
        $intruso->assertStatus(404)->assertJsonPath('error', 'MUESTRA_NO_ENCONTRADA');

        // El técnico propio sí lo ve.
        $propio = $this->withHeaders($this->evidenciaTechHeaders())
            ->getJson("/api/v1/lab-results/v2/historial/{$muestra->getKey()}");
        $propio->assertOk()->assertJsonCount(1, 'data.versiones');
    }
}
