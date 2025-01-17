<?php

namespace App\Service;

class FonctionService
{
    private array $months = [
        1 => 'Janvier',
        2 => 'Février',
        3 => 'Mars',
        4 => 'Avril',
        5 => 'Mai',
        6 => 'Juin',
        7 => 'Juillet',
        8 => 'Août',
        9 => 'Septembre',
        10 => 'Octobre',
        11 => 'Novembre',
        12 => 'Décembre',
    ];

    public function getMoisEnFrancais(\DateTime $date): string
    {
        return $this->months[$date->format('n')];
    }
}