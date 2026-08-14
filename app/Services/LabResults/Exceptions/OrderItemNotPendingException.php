<?php

namespace App\Services\LabResults\Exceptions;

use RuntimeException;

/**
 * El ítem de la orden no existe, no pertenece al tenant o no está pendiente
 * de captura (UC-02 / UC-03).
 */
class OrderItemNotPendingException extends RuntimeException
{
    public function __construct(private readonly int $orderItemId)
    {
        parent::__construct("El ítem de la orden {$orderItemId} no está pendiente de captura.");
    }

    public function getOrderItemId(): int
    {
        return $this->orderItemId;
    }
}