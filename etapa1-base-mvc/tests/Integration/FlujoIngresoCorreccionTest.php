<?php

declare(strict_types=1);

namespace LabResults\Tests\Integration;

use LabResults\Application\Command\CorregirResultadoCommand;
use LabResults\Application\Command\IngresarResultadoCommand;
use LabResults\Domain\Exception\MuestraRechazadaException;
use LabResults\Domain\Exception\ResultadoYaRegistradoException;
use LabResults\Domain\Exception\UnidadInvalidaException;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../bootstrap.php';

/**
 * PRUEBA DE INTEGRACIÓN — flujo vertical completo sobre la base SQLite:
 * pendientes -> captura -> validación -> corrección versionada.
 * Cubre los 5 puntos exigidos por la asignación (1 a 5).
 */
final class FlujoIngresoCorreccionTest extends TestCase
{
    private const MUESTRA_ACEPTADA = '11111111-1111-4111-8111-111111111111';
    private const MUESTRA_RECHAZADA = '22222222-2222-4222-8222-222222222222';

    private $c;

    protected function setUp(): void
    {
        // Base nueva y determinista para cada prueba.
        $this->c = lab_test_container();
    }

    /** 1) Ingreso correcto de resultado para muestra aceptada. */
    public function test_1_ingresa_resultado_para_muestra_aceptada(): void
    {
        // La muestra aparece en la cola de pendientes antes de capturar.
        $pendientes = $this->c->listarPendientes()->ejecutar();
        $this->assertNotEmpty($pendientes);
        $this->assertSame('TEST-BAR-0001', $pendientes[0]['barcode']);

        $v1 = $this->c->ingresarResultado()->ejecutar(new IngresarResultadoCommand(
            muestraId: self::MUESTRA_ACEPTADA,
            tipo: 'NUMERICO',
            valorNumerico: '14.5',
            valorTexto: null,
            unidad: 'g/dL'
        ));

        $this->assertSame(1, $v1->versionNumber);
        $this->assertFalse($v1->esCorreccion());
        $this->assertSame(14.5, $v1->contenido->valorNumerico);

        // Ya no está pendiente de captura.
        $idsPendientes = array_column($this->c->listarPendientes()->ejecutar(), 'muestra_id');
        $this->assertNotContains(self::MUESTRA_ACEPTADA, $idsPendientes);

        // Un segundo ingreso sobre la misma muestra se rechaza controladamente.
        $this->expectException(ResultadoYaRegistradoException::class);
        $this->c->ingresarResultado()->ejecutar(new IngresarResultadoCommand(
            muestraId: self::MUESTRA_ACEPTADA,
            tipo: 'NUMERICO',
            valorNumerico: '15.0',
            valorTexto: null,
            unidad: 'g/dL'
        ));
    }

    /** 2) Rechazo de resultado para muestra rechazada. */
    public function test_2_rechaza_resultado_para_muestra_rechazada(): void
    {
        try {
            $this->c->ingresarResultado()->ejecutar(new IngresarResultadoCommand(
                muestraId: self::MUESTRA_RECHAZADA,
                tipo: 'NUMERICO',
                valorNumerico: '92.0',
                valorTexto: null,
                unidad: 'mg/dL'
            ));
            $this->fail('Se esperaba MuestraRechazadaException.');
        } catch (MuestraRechazadaException $e) {
            $this->addToAssertionCount(1);
        }

        $this->assertCount(
            0,
            $this->c->resultados->listarPorSampleId(self::MUESTRA_RECHAZADA),
            'La muestra rechazada no debe tener versiones almacenadas.'
        );
    }

    /** 3) Rechazo de valor/tipo/unidad inválida. */
    public function test_3_rechaza_unidad_invalida(): void
    {
        $this->expectException(UnidadInvalidaException::class);

        $this->c->ingresarResultado()->ejecutar(new IngresarResultadoCommand(
            muestraId: self::MUESTRA_ACEPTADA,
            tipo: 'NUMERICO',
            valorNumerico: '14.5',
            valorTexto: null,
            unidad: 'mmol/L' // canónica de HEMO es g/dL
        ));
    }

    /** 4) La corrección genera una nueva versión. */
    public function test_4_correccion_genera_nueva_version(): void
    {
        $this->c->ingresarResultado()->ejecutar(new IngresarResultadoCommand(
            muestraId: self::MUESTRA_ACEPTADA,
            tipo: 'NUMERICO',
            valorNumerico: '14.5',
            valorTexto: null,
            unidad: 'g/dL'
        ));

        $v2 = $this->c->corregirResultado()->ejecutar(new CorregirResultadoCommand(
            muestraId: self::MUESTRA_ACEPTADA,
            motivo: 'Corrección controlada de prueba',
            tipo: 'NUMERICO',
            valorNumerico: '13.8',
            valorTexto: null,
            unidad: 'g/dL'
        ));

        $this->assertSame(2, $v2->versionNumber);
        $this->assertTrue($v2->esCorreccion());
        $this->assertSame(1, $v2->correctedFromVersion);
        $this->assertSame('Corrección controlada de prueba', $v2->correctionReason);
    }

    /** 5) La versión anterior permanece intacta tras la corrección. */
    public function test_5_version_anterior_permanece_intacta(): void
    {
        $v1 = $this->c->ingresarResultado()->ejecutar(new IngresarResultadoCommand(
            muestraId: self::MUESTRA_ACEPTADA,
            tipo: 'NUMERICO',
            valorNumerico: '14.5',
            valorTexto: null,
            unidad: 'g/dL'
        ));

        // Instantánea previa a la corrección.
        $snapshotV1 = [
            'version' => $v1->versionNumber,
            'valor' => $v1->contenido->valorNumerico,
            'unidad' => $v1->contenido->unidad,
            'motivo' => $v1->correctionReason,
        ];

        $this->c->corregirResultado()->ejecutar(new CorregirResultadoCommand(
            muestraId: self::MUESTRA_ACEPTADA,
            motivo: 'Ajuste del valor correcto',
            tipo: 'NUMERICO',
            valorNumerico: '13.8',
            valorTexto: null,
            unidad: 'g/dL'
        ));

        $historial = $this->c->resultados->listarPorSampleId(self::MUESTRA_ACEPTADA);

        // Dos filas inmutables; ninguna fue sobrescrita ni eliminada.
        $this->assertCount(2, $historial);
        $this->assertSame($snapshotV1['version'], $historial[0]->versionNumber);
        $this->assertSame($snapshotV1['valor'], $historial[0]->contenido->valorNumerico);
        $this->assertSame($snapshotV1['unidad'], $historial[0]->contenido->unidad);
        $this->assertNull($historial[0]->correctionReason, 'La v1 no debe adquirir datos de la corrección.');
        $this->assertSame(13.8, $historial[1]->contenido->valorNumerico);

        // El historial completo sigue disponible (regla 5 de la asignación).
        $vigente = $this->c->resultados->findCurrentBySampleId(self::MUESTRA_ACEPTADA);
        $this->assertSame(2, $vigente->versionNumber);
        $this->assertSame($historial[1]->id, $vigente->id);
    }
}
