<?php

declare(strict_types=1);

namespace LabResults\Domain\Repository;

use LabResults\Domain\Model\LabTestDefinition;

interface LabTestRepositoryInterface
{
    public function findById(int $id): ?LabTestDefinition;
}
