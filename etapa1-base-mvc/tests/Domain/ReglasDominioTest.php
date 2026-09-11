<?php

declare(strict_types=1);

namespace LabResults\Tests\Domain;

use LabResults\AppContainer;
use LabResults\Application\Command\CorregirResultadoCommand;
use LabResults\Application\Command\IngresarResultadoCommand;
use LabResults\Domain\Exception\MuestraRechazadaException;
use LabResults\Domain\Exception\ResultadoSinCambiosException;
use LabResults\Domain\Exception\TipoResultadoInvalidoException;
use LabResults\Domain\Exception\UnidadInvalidaException;
use LabResults\Domain\Exception\ValorNumericoInvalidoException;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../bootstrap.php';

/**
 * PRUEBAS DE DOMINIO — reglas centrales de la asignación:
 *  1. muestra rechazada no admite resultados;
 *  2. valor/tipo/unidad inválidos se rechazan;
 *  3. la corrección genera una nueva versión (y sin cambios reales, no).
 */
final class ReglasDominioTest extends TestCase
{
    private const MUESTRA_ACEPTADA = '11111111-1111-4111-8111-111111111111';
    private const MUESTRA_RECHAZADA = '22222222-2222-4222-8222-222222222222';

    public function test_muestra_rechazada_no_permite_ingresar_resultado(): void
    {
        $c = lab_test_container();

        try {
            $c->ingresarResultado()->ejecutar(new IngresarResultadoCommand(
                muestraId: self::MUESTRA_RECHAZADA,
                tipo: 'NUMERICO',
                valorNumerico: '92.0',
                valorTexto: null,
                unidad: 'mg/dL'
            ));
            $this->fail('Se esperaba MuestraRechazadaException.');
        } catch (MuestraRechazadaException $e) {
            $this->assertStringContainsString('RECHAZADA', $e->getMessage());
            // No debe haberse persistido ninguna versión.
            $this->assertCount(0, $c->resultados->listarPorSampleId(self::MUESTRA_RECHAZADA));
        }
    }

    public function test_valor_numerico_invalido_es_rechazado(): void
    {
        $c = lab_test_container();

        $this->expectException(ValorNumericoInvalidoException::class);
        $this->expectExceptionMessage('número válido');

        $c->ingresarResultado()->ejecutar(new IngresarResultadoCommand(
            muestraId: self::MUESTRA_ACEPTADA,
            tipo: 'NUMERICO',
            valorNumerico: 'abc',
            valorTexto: null,
            unidad: 'g/dL'
        ));
    }

    public function test_unidad_invalida_es_rechazada_y_no_se_persiste(): void
    {
        $c = lab_test_container();

        try {
            $c->ingresarResultado()->ejecutar(new IngresarResultadoCommand(
                muestraId: self::MUESTRA_ACEPTADA,
                tipo: 'NUMERICO',
                valorNumerico: '14.5',
                valorTexto: null,
                unidad: 'mmol/L' // unidad no válida para Hemoglobina (canónica: g/dL)
            ));
            $this->fail('Se esperaba UnidadInvalidaException.');
        } catch (UnidadInvalidaException $e) {
            $this->assertStringContainsString('g/dL', $e->getMessage());
        }

        $this->assertCount(0, $c->resultados->listarPorSampleId(self::MUESTRA_ACEPTADA));
    }

    public function test_tipo_incompatible_con_la_prueba_es_rechazado(): void
    {
        $c = lab_test_container();

        $this->expectException(TipoResultadoInvalidoException::class);

        // Urocultivo es TEXTO; aquí se intenta un NUMERICO sobre HEMO con tipo TEXTO.
        $c->ingresarResultado()->ejecutar(new IngresarResultadoCommand(
            muestraId: self::MUESTRA_ACEPTADA, // prueba HEMO requiere NUMERICO
            tipo: 'TEXTO',
            valorNumerico: null,
            valorTexto: 'Sin hallazgos',
            unidad: null
        ));
    }

    public function test_correccion_crea_version_nueva_sin_sobrescribir_la_anterior(): void
    {
        $c = lab_test_container();

        $v1 = $c->ingresarResultado()->ejecutar(new IngresarResultadoCommand(
            muestraId: self::MUESTRA_ACEPTADA,
            tipo: 'NUMERICO',
            valorNumerico: '14.5',
            valorTexto: null,
            unidad: 'g/dL'
        ));

        $v2 = $c->corregirResultado()->ejecutar(new CorregirResultadoCommand(
            muestraId: self::MUESTRA_ACEPTADA,
            motivo: 'Error de transcripción',
            tipo: 'NUMERICO',
            valorNumerico: '13.8',
            valorTexto: null,
            unidad: 'g/dL'
        ));

        $this->assertSame(1, $v1->versionNumber);
        $this->assertSame(2, $v2->versionNumber);
        $this->assertSame(1, $v2->correctedFromVersion);
        $this->assertSame(14.5, $v1->contenido->valorNumerico);   // v1 intacta en memoria…

        $historial = $c->resultados->listarPorSampleId(self::MUESTRA_ACEPTADA);
        $this->assertCount(2, $historial);                          // …y en persistencia
        $this->assertSame(14.5, $historial[0]->contenido->valorNumerico);
        $this->assertSame('Error de transcripción', $historial[1]->correctionReason);
        $this->assertSame($v2->id, $c->resultados->findCurrentBySampleId(self::MUESTRA_ACEPTADA)->id);
    }

    public function test_correccion_identica_al_vigente_es_rechazada(): void
    {
        $c = lab_test_container();

        $c->ingresarResultado()->ejecutar(new IngresarResultadoCommand(
            muestraId: self::MUESTRA_ACEPTADA,
            tipo: 'NUMERICO',
            valorNumerico: '14.5',
            valorTexto: null,
            unidad: 'g/dL'
        ));

        $this->expectException(ResultadoSinCambiosException::class);
        $this->assertCount(1, $c->resultados->listarPorSampleId(self::MUESTRA_ACEPTADA));

        $c->corregirResultado()->ejecutar(new CorregirResultadoCommand(
            muestraId: self::MUESTRA_ACEPTADA,
            motivo: 'Intento sin cambios reales',
            tipo: 'NUMERICO',
            valorNumerico: '14.5',
            valorTexto: null,
            unidad: 'g/dL'
        ));
    }
}
