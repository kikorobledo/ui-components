<?php

namespace App\Enums\Enums;

enum AreaEnum:string
{

    case RPP = 'rpp';
    case CATASTRO = 'catastro';
    case REGIONAL_1 = 'regional_1';
    case REGIONAL_2 = 'regional_2';
    case REGIONAL_3 = 'regional_3';
    case REGIONAL_4 = 'regional_4';
    case REGIONAL_5 = 'regional_5';
    case REGIONAL_6 = 'regional_6';
    case REGIONAL_7 = 'regional_7';

    public function label(): string
    {
        return match($this) {
            self::RPP => 'Dirección del Regsitro Público de la Propiedad',
            self::CATASTRO => 'Dirección de Catastro',
            self::REGIONAL_1 => 'Coordinación Regional 1 Lerma Chapala (Zamora)',
            self::REGIONAL_2 => 'Coordinación Regional 2 Bajio (La Piedad)',
            self::REGIONAL_3 => 'Coordinación Regional 3 Tepalcatepec (Apatzingan)',
            self::REGIONAL_4 => 'Coordinación Regional 4 Purhépecha (Uruapan)',
            self::REGIONAL_5 => 'Coordinación Regional 5 Tierra Caliente (Huetamo)',
            self::REGIONAL_6 => 'Coordinación Regional 6 Sierra Costa (Lazaro Cardenas)',
            self::REGIONAL_7 => 'Coordinación Regional 7 Oriente (Ciudad Hidalgo)',
        };
    }

}
