<?php


namespace App\Domain;


class Species
{
    public static function getSpecies(): array
    {
        return [
          'man',
          'woman',
          'horse',
          'fish',
          'dog',
          'whale',
          'mosquito',
          'cat',
        ];
    }
}