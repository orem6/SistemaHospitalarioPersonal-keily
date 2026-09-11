<?php

namespace App\Application\LabResults\UseCase;

use App\Application\LabResults\Command\CorregirResultadoCommand;
use App\Domain\LabResults\Contract\MuestraReaderInterface;
use App\Domain\LabResults\Contract\PruebaReaderInterface;
use App\Application\LabResults\Contract\TransactionManagerInterface;
use App\Domain\LabResults\Contract\VersionResultadoRepositoryInterface;
use App\Domain\LabResults\Exception\DomainRuleException;
use App\Domain\LabResults\Exception\MuestraNoEncontradaException;
use App\Domain\LabResults\Exception\ResultadoSinCambiosException;
use App\Domain\LabResults\Model\ContenidoResultado;
use App\Domain\LabResults\Model\VersionResultado;
use App\Domain\LabResults\Policy\PoliticaCorreccion;
use App\Domain\LabResults\Service\ValidadorContenidoResultado;
use Illuminate\Support\Carbon;

/**
 * Flujo: resultado existente -> corrección solicitada -> NUEVA versión
 * (n + 1) -> resultado anterior conservado. Transaccional.
 */
final class CorregirResultadoUseCase
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
    public function ejecutar(CorregirResultadoCommand $comando): VersionResultado
    {
        return $this->transacciones->ejecutar(function () use ($comando): VersionResultado {
            // El motivo es obligatorio para la trazabilidad.
            PoliticaCorreccion::assertMotivoValido($comando->motivo);

            $muestra = $this->muestras->obtenerPorId($comando->muestraId);

            if ($muestra === null || $muestra->tenantId !== $comando->tenantId) {
                throw new MuestraNoEncontradaException($comando->muestraId);
            }

            $vigente = $this->versiones->vigentePorMuestra($muestra->id);
            PoliticaCorreccion::assertExisteResultadoActual($vigente, $muestra->id);

            $definicion = $this->pruebas->obtenerPorId((int) $vigente->pruebaId)
                ?? throw new DomainRuleException('La definición de la prueba no existe en el catálogo.');

            $nuevoContenido = ContenidoResultado::crear(
                $comando->tipo,
                $comando->valorNumerico,
                $comando->valorTexto,
                $comando->unidad
            );
            $this->validador->validar($definicion, $nuevoContenido);

            if ($vigente->contenido->igualA($nuevoContenido)) {
                throw new ResultadoSinCambiosException();
            }

            // Nueva versión (n + 1); la fila anterior jamás se modifica.
            $nueva = VersionResultado::correccion(
                $muestra,
                $vigente,
                $nuevoContenido,
                $comando->motivo,
                Carbon::now()->toDateTimeString()
            );

            $id = $this->versiones->registrar($nueva);

            return $this->versiones->obtenerPorId($id) ?? $nueva;
        });
    }
}
