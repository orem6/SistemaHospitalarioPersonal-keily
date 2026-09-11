<?php

namespace Tests\Unit\LabResults;

use App\Domain\LabResults\Exception\DomainRuleException;
use App\Domain\LabResults\Exception\TipoResultadoInvalidoException;
use App\Domain\LabResults\Exception\UnidadInvalidaException;
use App\Domain\LabResults\Exception\ValorNumericoInvalidoException;
use App\Domain\LabResults\Exception\ValorTextoInvalidoException;
use App\Domain\LabResults\Model\ContenidoResultado;
use App\Domain\LabResults\Model\EstadoAceptacion;
use App\Domain\LabResults\Model\MuestraSnapshot;
use App\Domain\LabResults\Model\TipoResultado;
use App\Domain\LabResults\Model\VersionResultado;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Reglas de dominio puras, sin framework ni base de datos.
 */
final class DomainRulesTest extends TestCase
{
    public static function contenidosInvalidos(): array
    {
        return [
            'tipo desconocido' => ['QUIMICO', null, null, null, TipoResultadoInvalidoException::class],
            'numerico sin valor' => ['NUMERICO', null, null, null, ValorNumericoInvalidoException::class],
            'numerico no numerico' => ['NUMERICO', 'abc', null, null, ValorNumericoInvalidoException::class],
            'texto vacio' => ['TEXTO', null, '   ', null, ValorTextoInvalidoException::class],
            'texto con unidad' => ['TEXTO', null, 'Negativo', 'u/mL', UnidadInvalidaException::class],
        ];
    }

    #[DataProvider('contenidosInvalidos')]
    public function test_contenido_rechaza_formas_invalidas(
        string $tipo,
        ?string $numerico,
        ?string $texto,
        ?string $unidad,
        string $excepcionEsperada,
    ): void {
        try {
            ContenidoResultado::crear($tipo, $numerico, $texto, $unidad);
            $this->fail("Se esperaba {$excepcionEsperada}");
        } catch (DomainRuleException $e) {
            $this->assertInstanceOf($excepcionEsperada, $e);
        }
    }

    #[Test]
    public function solo_muestras_aceptadas_permite_resultados(): void
    {
        $this->assertTrue(EstadoAceptacion::Aceptada->permiteIngresarResultados());
        $this->assertFalse(EstadoAceptacion::Rechazada->permiteIngresarResultados());
        $this->assertFalse(EstadoAceptacion::Pendiente->permiteIngresarResultados());

        // Valores legados sin decisión registrada se tratan como pendientes.
        $this->assertSame(EstadoAceptacion::Pendiente, EstadoAceptacion::desdeValor(null));
        $this->assertSame(EstadoAceptacion::Pendiente, EstadoAceptacion::desdeValor(''));
        $this->assertSame(EstadoAceptacion::Rechazada, EstadoAceptacion::desdeValor('rechazada'));
    }

    #[Test]
    public function captura_inicial_es_siempre_version_uno(): void
    {
        $muestra = new MuestraSnapshot(
            id: 7,
            tenantId: 'tenant-x',
            barcode: 'BC-X',
            pruebaId: 1,
            estadoAceptacion: EstadoAceptacion::Aceptada,
            motivoRechazo: null,
            collectadaEn: null,
        );

        $v1 = VersionResultado::capturaInicial(
            $muestra,
            ContenidoResultado::crear('NUMERICO', '10.0', null, null),
            '2026-08-21 10:00:00'
        );

        $this->assertSame(1, $v1->numeroVersion);
        $this->assertNull($v1->corrigeAVersion);
        $this->assertFalse($v1->esCorreccion());
    }

    #[Test]
    public function correccion_incrementa_version_y_conserva_la_anterior(): void
    {
        $muestra = new MuestraSnapshot(
            id: 7,
            tenantId: 'tenant-x',
            barcode: 'BC-X',
            pruebaId: 1,
            estadoAceptacion: EstadoAceptacion::Aceptada,
            motivoRechazo: null,
            collectadaEn: null,
        );

        $v1 = VersionResultado::capturaInicial(
            $muestra,
            ContenidoResultado::crear('NUMERICO', '10.0', null, null),
            '2026-08-21 10:00:00'
        );

        $v2 = VersionResultado::correccion(
            $muestra,
            $v1,
            ContenidoResultado::crear('NUMERICO', '12.0', null, null),
            'Corrección por calibración.',
            '2026-08-21 11:00:00'
        );

        $this->assertSame(2, $v2->numeroVersion);
        $this->assertSame(1, $v2->corrigeAVersion);
        $this->assertTrue($v2->esCorreccion());

        // La entidad anterior es inmutable: sigue siendo la versión 1 original.
        $this->assertSame(1, $v1->numeroVersion);
        $this->assertNull($v1->corrigeAVersion);
    }

    #[Test]
    public function rehidratacion_desde_almacenamiento_es_simetrica(): void
    {
        $original = ContenidoResultado::crear('TEXTO', null, 'Negativo', null);
        $rehidratado = ContenidoResultado::desdeAlmacenamiento(
            TipoResultado::Texto,
            $original->valorNumerico,
            $original->valorTexto,
            $original->unidad,
        );

        $this->assertSame($original->valorTexto, $rehidratado->valorTexto);
        $this->assertTrue($original->igualA($rehidratado));
    }
}
