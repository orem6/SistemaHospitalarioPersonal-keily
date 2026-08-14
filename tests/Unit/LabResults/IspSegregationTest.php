<?php

namespace Tests\Unit\LabResults;

use App\Http\Controllers\Api\V1\LabResults\LabResultCorrectionController;
use App\Http\Controllers\Api\V1\LabResults\LabResultEntryController;
use App\Http\Controllers\Api\V1\LabResults\LabResultPublishController;
use App\Http\Controllers\Api\V1\LabResults\PendingResultsController;
use App\Http\Controllers\Api\V1\LabResults\PublishedResultsController;
use App\Services\LabResults\Contracts\PendingResultCorrector;
use App\Services\LabResults\Contracts\PendingResultsProvider;
use App\Services\LabResults\Contracts\PublishedResultReader;
use App\Services\LabResults\Contracts\ResultEntryWriter;
use App\Services\LabResults\Contracts\ResultPublisher;
use App\Services\LabResults\Contracts\ResultValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use ReflectionNamedType;
use Tests\TestCase;

/**
 * Evidencia verificable de la aplicación de ISP en ASII-19:
 * cada cliente depende únicamente del contrato que necesita usar.
 */
class IspSegregationTest extends TestCase
{
    /**
     * @param class-string $client
     * @param class-string $expectedContract
     */
    #[DataProvider('controllersContracts')]
    public function test_controllers_depend_only_on_their_segregated_contract(string $client, string $expectedContract): void
    {
        $constructor = (new ReflectionClass($client))->getConstructor();

        $this->assertNotNull($constructor, "{$client} debe declarar un constructor de inyección.");

        $parameters = $constructor->getParameters();
        $this->assertCount(1, $parameters, "{$client} debe depender de un único contrato, no de una interfaz monolítica.");

        $type = $parameters[0]->getType();
        $this->assertInstanceOf(ReflectionNamedType::class, $type);
        $this->assertSame($expectedContract, $type->getName(), "{$client} debe depender solo de {$expectedContract}.");
    }

    /**
     * @param class-string $contract
     */
    #[DataProvider('contractsMethods')]
    public function test_each_contract_exposes_a_single_focused_responsibility(string $contract, int $expectedMethods): void
    {
        $methods = (new ReflectionClass($contract))->getMethods();

        $this->assertCount($expectedMethods, $methods, "{$contract} debe exponer una única responsabilidad enfocada.");
    }

    public static function controllersContracts(): array
    {
        return [
            'UC-01 pending (técnico)' => [PendingResultsController::class, PendingResultsProvider::class],
            'UC-02 entry (técnico)' => [LabResultEntryController::class, ResultEntryWriter::class],
            'UC-03 correct (técnico)' => [LabResultCorrectionController::class, PendingResultCorrector::class],
            'UC-04 publish (técnico)' => [LabResultPublishController::class, ResultPublisher::class],
            'UC-05 published (médico)' => [PublishedResultsController::class, PublishedResultReader::class],
        ];
    }

    public static function contractsMethods(): array
    {
        return [
            'UC-01' => [PendingResultsProvider::class, 1],
            'UC-02' => [ResultEntryWriter::class, 1],
            'UC-03' => [PendingResultCorrector::class, 1],
            'UC-04' => [ResultPublisher::class, 1],
            'UC-05' => [PublishedResultReader::class, 1],
            'UC-06' => [ResultValidator::class, 1],
        ];
    }
}