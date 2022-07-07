<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class OperationClass extends Enum
{
    const Inmediata = "Inmediata";
    const Programada = "Programada";
    const Interbancaria = "Interbancaria";
}
