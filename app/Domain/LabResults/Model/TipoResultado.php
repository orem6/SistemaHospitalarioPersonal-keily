<?php

namespace App\Domain\LabResults\Model;

enum TipoResultado: string
{
    case Numerico = 'NUMERICO';
    case Texto = 'TEXTO';
}
