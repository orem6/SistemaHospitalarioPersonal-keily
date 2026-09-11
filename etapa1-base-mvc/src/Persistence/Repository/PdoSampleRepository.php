<?php

declare(strict_types=1);

namespace LabResults\Persistence\Repository;

use LabResults\Domain\Model\Sample;
use LabResults\Domain\Model\SampleStatus;
use LabResults\Domain\Repository\SampleRepositoryInterface;
use PDO;

/**
 * Adaptador PDO del puerto SampleRepositoryInterface.
 * Solo consultas preparadas; aquí NO viven reglas de negocio,
 * únicamente el mapeo fila -> entidad de dominio.
 */
final class PdoSampleRepository implements SampleRepositoryInterface
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findById(string $id): ?Sample
    {
        $stmt = $this->pdo->prepare('SELECT * FROM samples WHERE id = :id');
        $stmt->execute([':id' => $id]);

        $fila = $stmt->fetch();

        return $fila === false ? null : $this->mapear($fila);
    }

    public function listarPendientesDeCaptura(): array
    {
        $sql = "SELECT s.*
                FROM samples s
                WHERE s.status = 'ACEPTADA'
                  AND NOT EXISTS (
                      SELECT 1 FROM lab_result_versions v WHERE v.sample_id = s.id
                  )
                ORDER BY s.collected_at";

        $stmt = $this->pdo->query($sql);

        return array_map($this->mapear(...), $stmt->fetchAll());
    }

    public function listarTodas(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM samples ORDER BY barcode');

        return array_map($this->mapear(...), $stmt->fetchAll());
    }

    private function mapear(array $fila): Sample
    {
        return new Sample(
            id: (string) $fila['id'],
            tenantId: (string) $fila['tenant_id'],
            patientRef: (string) $fila['patient_ref'],
            barcode: (string) $fila['barcode'],
            testId: (int) $fila['test_id'],
            status: SampleStatus::from((string) $fila['status']),
            rejectionReason: $fila['rejection_reason'],
            collectedAt: $fila['collected_at'],
        );
    }
}
