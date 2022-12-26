<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class TrackingStatus extends Enum
{
    const Pendiente = 'Pendiente';
    const Completado = 'Completado';
    const EnCurso = 'En curso';
    const SeguimientoIncumplido = 'Seguimiento incumplido';

}
