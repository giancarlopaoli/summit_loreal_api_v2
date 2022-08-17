<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class OperationType extends Enum
{
    const Compra = "Compra";
    const Venta = "Venta";
    const Interbancaria = "Interbancaria";
}
