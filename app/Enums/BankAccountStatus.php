<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class BankAccountStatus extends Enum
{
    const Activo =   1;
    const Inactivo = 2;
    const Pendiente = 3;
    const Rechazado = 4;
}
