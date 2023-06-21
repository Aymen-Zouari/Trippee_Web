<?php

namespace App\Enum;

use Doctrine\ORM\Mapping as ORM;
use MyCLabs\Enum\Enum as MyCLabsEnum;



#[ORM\Embeddable]
class Etat extends MyCLabsEnum
{
    // Définissez ici les valeurs possibles de votre énumération
    const ENABLED = 'enabled';
    const DISABLED = 'disabled';
    
    public static function getEnabled(): self
    {
        return static::ENABLED();
    }

    public static function getDisabled(): self
    {
        return static::DISABLED();
    }
}
