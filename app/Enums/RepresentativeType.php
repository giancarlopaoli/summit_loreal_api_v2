<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class RepresentativeType extends Enum
{
    const Socio =   "Socio";
    const RepresentanteLegal =   "Representante Legal";
}
