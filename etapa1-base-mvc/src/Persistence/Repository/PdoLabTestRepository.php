<?php

declare(strict_types=1);

namespace LabResults\Persistence\Repository;

use LabResults\Domain\Model\LabTestDefinition;
use LabResults\Domain\Model\ResultType;
use LabResults\Domain\Repository\LabTestRepositoryInterface;
use PDO;

final class PdoLabTestRepository implements LabTestRepositoryInterface
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findById(int $id): ?LabTestDefinition
    {
        $stmt = $this->pdo->prepare('SELECT * FROM lab_test_definitions WHERE id = :id');
        $stmt->execute([':id' => $id]);

        $fila = $stmt->fetch();

        return $fila === false ? null : $this->mapear($fila);
    }

    private function mapear(array $fila): LabTestDefinition
    {
        return new LabTestDefinition(
            id: (int) $fila['id'],
            code: (string) $fila['code'],
            name: (string) $fila['name'],
            resultType: ResultType::from((string) $fila['result_type']),
            unit: $fila['unit'],
        );
    }
}
