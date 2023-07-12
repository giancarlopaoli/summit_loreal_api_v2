<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class DocumentType extends Enum
{
    const Comprobante = 'Comprobante';
    const Detraccion =   'Detraccion';
    const Firma1 =   '1ra firma';
    const Firma2 =   '2da firma';
}
