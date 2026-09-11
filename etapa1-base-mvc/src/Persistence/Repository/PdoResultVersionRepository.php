<?php

declare(strict_types=1);

namespace LabResults\Persistence\Repository;

use LabResults\Domain\Model\ContenidoResultado;
use LabResults\Domain\Model\ResultType;
use LabResults\Domain\Model\ResultVersion;
use LabResults\Domain\Repository\ResultVersionRepositoryInterface;
use PDO;

/**
 * Adaptador PDO del puerto ResultVersionRepositoryInterface.
 *
 * GARANTÍA DE INMUTABILIDAD:
 * esta clase solo ejecuta INSERT y SELECT. No existe ningún
 * UPDATE ni DELETE sobre lab_result_versions, de modo que una
 * corrección jamás puede sobrescribir la versión anterior.
 */
final class PdoResultVersionRepository implements ResultVersionRepositoryInterface
{
    private const INSERT_SQL =
        'INSERT INTO lab_result_versions
             (sample_id, test_id, tenant_id, version_number, result_type,
              numeric_value, text_value, unit, corrected_from_version, correction_reason)
         VALUES
             (:sample_id, :test_id, :tenant_id, :version_number, :result_type,
              :numeric_value, :text_value, :unit, :corrected_from_version, :correction_reason)';

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function guardar(ResultVersion $version): int
    {
        $stmt = $this->pdo->prepare(self::INSERT_SQL);
        $stmt->execute([
            ':sample_id' => $version->sampleId,
            ':test_id' => $version->testId,
            ':tenant_id' => $version->tenantId,
            ':version_number' => $version->versionNumber,
            ':result_type' => $version->contenido->tipo->value,
            ':numeric_value' => $version->contenido->valorNumerico,
            ':text_value' => $version->contenido->valorTexto,
            ':unit' => $version->contenido->unidad,
            ':corrected_from_version' => $version->correctedFromVersion,
            ':correction_reason' => $version->correctionReason,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findCurrentBySampleId(string $sampleId): ?ResultVersion
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM lab_result_versions WHERE sample_id = :sample_id
             ORDER BY version_number DESC LIMIT 1'
        );
        $stmt->execute([':sample_id' => $sampleId]);

        $fila = $stmt->fetch();

        return $fila === false ? null : $this->mapear($fila);
    }

    public function listarPorSampleId(string $sampleId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM lab_result_versions WHERE sample_id = :sample_id ORDER BY version_number ASC'
        );
        $stmt->execute([':sample_id' => $sampleId]);

        return array_map($this->mapear(...), $stmt->fetchAll());
    }

    public function findById(int $id): ?ResultVersion
    {
        $stmt = $this->pdo->prepare('SELECT * FROM lab_result_versions WHERE id = :id');
        $stmt->execute([':id' => $id]);

        $fila = $stmt->fetch();

        return $fila === false ? null : $this->mapear($fila);
    }

    private function mapear(array $fila): ResultVersion
    {
        return new ResultVersion(
            id: (int) $fila['id'],
            sampleId: (string) $fila['sample_id'],
            testId: (int) $fila['test_id'],
            tenantId: (string) $fila['tenant_id'],
            versionNumber: (int) $fila['version_number'],
            contenido: ContenidoResultado::desdeAlmacenamiento(
                ResultType::from((string) $fila['result_type']),
                $fila['numeric_value'] === null ? null : (float) $fila['numeric_value'],
                $fila['text_value'],
                $fila['unit'],
            ),
            correctedFromVersion: $fila['corrected_from_version'] === null ? null : (int) $fila['corrected_from_version'],
            correctionReason: $fila['correction_reason'],
            capturedAt: $fila['captured_at'] ?? null,
        );
    }
}
