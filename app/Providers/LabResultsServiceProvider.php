<?php

namespace App\Providers;

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
 */
class LabResultsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PendingResultsProvider::class, EloquentPendingResultsProvider::class);
        $this->app->bind(ResultEntryWriter::class, LabResultEntryService::class);
        $this->app->bind(PendingResultCorrector::class, LabResultCorrectionService::class);
        $this->app->bind(ResultPublisher::class, ResultPublicationService::class);
        $this->app->bind(PublishedResultReader::class, EloquentPublishedResultReader::class);
        $this->app->bind(ResultValidator::class, StrictResultValidator::class);
    }
}