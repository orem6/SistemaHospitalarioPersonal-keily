<?php

declare(strict_types=1);

namespace LabResults\Domain\Model;

enum ResultType: string
{
    case Numerico = 'NUMERICO';
    case Texto = 'TEXTO';
}
