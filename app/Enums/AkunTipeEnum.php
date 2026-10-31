<?php

namespace App\Enums;

use App\Concerns\EnumTrait;
use BenSampo\Enum\Enum;

final class AkunTipeEnum extends Enum
{
    use EnumTrait;

    const ASET = 'ASET';

    const LIABILITAS = 'LIABILITAS';

    const EKUITAS = 'EKUITAS';

    const PENDAPATAN = 'PENDAPATAN';

    const BEBAN = 'BEBAN';

    public static function getDescription(mixed $value): string
    {
        return match ($value) {
            self::ASET => 'Aset',
            self::LIABILITAS => 'Liabilitas',
            self::EKUITAS => 'Ekuitas',
            self::PENDAPATAN => 'Pendapatan',
            self::BEBAN => 'Beban',
            default => parent::getDescription($value),
        };
    }
}
