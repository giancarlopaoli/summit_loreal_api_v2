<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class UserStatus extends Enum
{
    const Activo = "Activo";
    const Inactivo = "Inactivo";
    const PorAprobar = "Por Aprobar";
    const Bloqueado = "Bloqueado";
}
