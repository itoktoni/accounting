<?php

namespace App\Enums;

use App\Concerns\EnumTrait;
use BenSampo\Enum\Enum;

final class StokJenisEnum extends Enum
{
    use EnumTrait;

    const MASUK = 'MASUK';

    const KELUAR = 'KELUAR';

    const OPNAME = 'OPNAME';

    const PRODUKSI = 'PRODUKSI';

    const RETUR = 'RETUR';

    public static function getDescription(mixed $value): string
    {
        return match ($value) {
            self::MASUK => 'Stok Masuk',
            self::KELUAR => 'Stok Keluar',
            self::OPNAME => 'Stok Opname',
            self::PRODUKSI => 'Hasil Produksi',
            self::RETUR => 'Retur',
            default => parent::getDescription($value),
        };
    }
}
