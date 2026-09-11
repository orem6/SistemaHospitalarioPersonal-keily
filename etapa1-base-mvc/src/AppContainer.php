<?php

declare(strict_types=1);

namespace LabResults;

use LabResults\Application\UseCase\ConsultarHistorialUseCase;
use LabResults\Application\UseCase\CorregirResultadoUseCase;
use LabResults\Application\UseCase\IngresarResultadoUseCase;
use LabResults\Application\UseCase\ListarResultadosPendientesUseCase;
use LabResults\Domain\Service\ResultContentValidator;
use LabResults\Persistence\Database\Connection;
use LabResults\Persistence\Repository\PdoLabTestRepository;
use LabResults\Persistence\Repository\PdoResultVersionRepository;
use LabResults\Persistence\Repository\PdoSampleRepository;

/**
 * Ensamblado manual de las capas (Composition Root).
 * Único punto donde se conectan Domain <-> Persistence.
 */
final class AppContainer
{
    public readonly Connection $connection;
    public readonly PdoSampleRepository $muestras;
    public readonly PdoLabTestRepository $pruebas;
    public readonly PdoResultVersionRepository $resultados;
    public readonly ResultContentValidator $validador;

    private function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $pdo = $connection->pdo();

        $this->muestras = new PdoSampleRepository($pdo);
        $this->pruebas = new PdoLabTestRepository($pdo);
        $this->resultados = new PdoResultVersionRepository($pdo);
        $this->validador = new ResultContentValidator();
    }

    public static function conSqlite(string $rutaSqlite): self
    {
        return new self(Connection::connect($rutaSqlite));
    }

    public function ingresarResultado(): IngresarResultadoUseCase
    {
        return new IngresarResultadoUseCase(
            $this->muestras,
            $this->pruebas,
            $this->resultados,
            $this->validador
        );
    }

    public function corregirResultado(): CorregirResultadoUseCase
    {
        return new CorregirResultadoUseCase(
            $this->muestras,
            $this->pruebas,
            $this->resultados,
            $this->validador
        );
    }

    public function listarPendientes(): ListarResultadosPendientesUseCase
    {
        return new ListarResultadosPendientesUseCase($this->muestras, $this->pruebas);
    }

    public function consultarHistorial(): ConsultarHistorialUseCase
    {
        return new ConsultarHistorialUseCase($this->muestras, $this->pruebas, $this->resultados);
    }
}
