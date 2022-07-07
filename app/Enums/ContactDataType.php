<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class ContactDataType extends Enum
{
    const Fijo =   0;
    const Celular =   1;
    const Email = 2;
}
