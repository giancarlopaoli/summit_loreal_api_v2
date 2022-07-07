<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class ExecutiveType extends Enum
{
    const TiempoCompleto = "Tiempo Completo";
    const Freelance = "Freelance";
}
