<?php

namespace App\Providers;

use App\Application\LabResults\Contract\TransactionManagerInterface;
use App\Domain\LabResults\Contract\MuestraReaderInterface;
use App\Domain\LabResults\Contract\PruebaReaderInterface;
use App\Domain\LabResults\Contract\VersionResultadoRepositoryInterface;
use App\Domain\LabResults\Service\ValidadorContenidoResultado;
use App\Infrastructure\LabResults\Persistence\Eloquent\EloquentMuestraReader;
use App\Infrastructure\LabResults\Persistence\Eloquent\EloquentPruebaReader;
use App\Infrastructure\LabResults\Persistence\Eloquent\EloquentTransactionManager;
use App\Infrastructure\LabResults\Persistence\Eloquent\EloquentVersionResultadoRepository;
use App\Services\LabResults\Contracts\PendingResultCorrector;
use App\Services\LabResults\Contracts\PendingResultsProvider;
use App\Services\LabResults\Contracts\PublishedResultReader;
use App\Services\LabResults\Contracts\ResultEntryWriter;
use App\Services\LabResults\Contracts\ResultPublisher;
use App\Services\LabResults\Contracts\ResultValidator;
use App\Services\LabResults\EloquentPendingResultsProvider;
use App\Services\LabResults\EloquentPublishedResultReader;
use App\Services\LabResults\LabResultCorrectionService;
use App\Services\LabResults\LabResultEntryService;
use App\Services\LabResults\ResultPublicationService;
use App\Services\LabResults\StrictResultValidator;
use Illuminate\Support\ServiceProvider;

/**
 * Punto de composición del módulo ASII-19. Cada cliente (controlador) se
 * inyecta únicamente el contrato que necesita; las implementaciones se
 * resuelven por el contenedor, no por los clientes.
 *
 * Etapa 2: se agregan los bindings Repository Pattern (Domain -> Infrastructure)
 * para el flujo versionado, sin alterar los bindings de la semana 2.
 */
class LabResultsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Semana 2 (ISP) — se conserva intacto.
        $this->app->bind(PendingResultsProvider::class, EloquentPendingResultsProvider::class);
        $this->app->bind(ResultEntryWriter::class, LabResultEntryService::class);
        $this->app->bind(PendingResultCorrector::class, LabResultCorrectionService::class);
        $this->app->bind(ResultPublisher::class, ResultPublicationService::class);
        $this->app->bind(PublishedResultReader::class, EloquentPublishedResultReader::class);
        $this->app->bind(ResultValidator::class, StrictResultValidator::class);

        // Etapa 2 — flujo versionado (Repository Pattern).
        $this->app->bind(MuestraReaderInterface::class, EloquentMuestraReader::class);
        $this->app->bind(PruebaReaderInterface::class, EloquentPruebaReader::class);
        $this->app->bind(VersionResultadoRepositoryInterface::class, EloquentVersionResultadoRepository::class);
        $this->app->bind(TransactionManagerInterface::class, EloquentTransactionManager::class);
        $this->app->singleton(ValidadorContenidoResultado::class);
    }
}
