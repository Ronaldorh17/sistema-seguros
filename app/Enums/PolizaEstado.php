<?php

namespace App\Enums;

enum PolizaEstado: string
{
    case BORRADOR = 'BORRADOR';
    case PENDIENTE_REVISION = 'PENDIENTE_REVISION';
    case OBSERVADA = 'OBSERVADA';
    case VALIDADA = 'VALIDADA';
    case BLOQUEADA = 'BLOQUEADA';
}