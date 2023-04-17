<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class AlertStatus extends Enum
{
    const Activo = 'Activo';
    const Eliminado = 'Eliminado';
    const Atendido = 'Atendidoo';
}
