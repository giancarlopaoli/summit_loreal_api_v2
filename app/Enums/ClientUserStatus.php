<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class ClientUserStatus extends Enum
{
    const Active   = "Activo";
    const Asignado = "Asignado";
    const Inactivo = "Inactivo";
}
