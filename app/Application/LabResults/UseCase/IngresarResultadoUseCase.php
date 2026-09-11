<?php

namespace App\Application\LabResults\UseCase;

use App\Application\LabResults\Command\IngresarResultadoCommand;
use App\Domain\LabResults\Contract\MuestraReaderInterface;
use App\Domain\LabResults\Contract\PruebaReaderInterface;
use App\Application\LabResults\Contract\TransactionManagerInterface;
use App\Domain\LabResults\Contract\VersionResultadoRepositoryInterface;
use App\Domain\LabResults\Exception\DomainRuleException;
use App\Domain\LabResults\Model\ContenidoResultado;
use App\Domain\LabResults\Model\VersionResultado;
use App\Domain\LabResults\Policy\PoliticaIngresoResultado;
use App\Domain\LabResults\Service\ValidadorContenidoResultado;
use Illuminate\Support\Carbon;

/**
 * Flujo: muestra aceptada -> resultado pendiente -> captura -> validación
 * -> persistencia como versión 1 (transaccional).
 */
final class IngresarResultadoUseCase
{
    public function __construct(
        private readonly MuestraReaderInterface $muestras,
        private readonly PruebaReaderInterface $pruebas,
        private readonly VersionResultadoRepositoryInterface $versiones,
        private readonly ValidadorContenidoResultado $validador,
        private readonly TransactionManagerInterface $transacciones,
    ) {
    }

    /**
     * @throws DomainRuleException rechazo controlado por regla de negocio
     */
    public function ejecutar(IngresarResultadoCommand $comando): VersionResultado
    {
        return $this->transacciones->ejecutar(function () use ($comando): VersionResultado {
            $muestra = $this->muestras->obtenerPorId($comando->muestraId);

            if ($muestra === null || $muestra->tenantId !== $comando->tenantId) {
                throw new \App\Domain\LabResults\Exception\MuestraNoEncontradaException($comando->muestraId);
            }

            // Regla central: solo muestras aceptadas (rechazada => excepción).
            PoliticaIngresoResultado::assertMuestraPermiteIngreso($muestra);

            if ($muestra->pruebaId === null) {
                throw new DomainRuleException('La muestra no tiene una prueba de laboratorio asociada.');
            }

            $vigente = $this->versiones->vigentePorMuestra($muestra->id);
            PoliticaIngresoResultado::assertSinResultadoVigente($vigente, $muestra->barcode);

            $definicion = $this->pruebas->obtenerPorId($muestra->pruebaId)
                ?? throw new DomainRuleException('La definición de la prueba no existe en el catálogo.');

            $contenido = ContenidoResultado::crear(
                $comando->tipo,
                $comando->valorNumerico,
                $comando->valorTexto,
                $comando->unidad
            );
            $this->validador->validar($definicion, $contenido);

            $nueva = VersionResultado::capturaInicial(
                $muestra,
                $contenido,
                Carbon::now()->toDateTimeString()
            );

            $id = $this->versiones->registrar($nueva);

            return $this->versiones->obtenerPorId($id) ?? $nueva;
        });
    }
}
