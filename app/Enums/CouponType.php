<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class CouponType extends Enum
{
    const Comision   = "Comision";
    const Porcentaje = "Porcentaje";
}
