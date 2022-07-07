<?php

namespace App\Enums;

use App\Models\Operation;
use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class CouponClass extends Enum
{
    const Normal =   "Normal";
    const PrimeraOperation =   "Primera Operacion";
}
