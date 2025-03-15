<?php

namespace App\Enum;

enum CountryTaxRate: string
{
    case DE = 'DE';
    case IT = 'IT';
    case FR = 'FR';
    case GR = 'GR';

    public function rate(): float
    {
        return match ($this) {
            self::DE => 19.0,
            self::IT => 22.0,
            self::FR => 20.0,
            self::GR => 24.0,
        };
    }
}