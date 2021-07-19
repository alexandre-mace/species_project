<?php


namespace App\Domain;


use App\Entity\Question;
use App\Entity\Survey;

class SpeciesPicker
{
    public function getSpeciesForQuestion(Survey $survey)
    {
        $alreadyAnsweredSpecies = array_reduce(array_map(function (Question $question) {
                return [$question->getSpeciesA(), $question->getSpeciesB()];
            }, $survey->getQuestions()->toArray()), function ($carry, $item) {
                $carry[] = $item[0];
                $carry[] = $item[1];
                return $carry;
            }) ?? [];

        $speciesA = $this->getRandomSpeciesToPick(Species::getSpecies(), $alreadyAnsweredSpecies, []);

        $alreadyBSpeciesFaced = array_map(function (Question $question) {
            return $question->getSpeciesB();
        }, array_filter($survey->getQuestions()->toArray(), function (Question $question) use ($speciesA) {
            return $question->getSpeciesA() === $speciesA;
        }));

        $speciesB = $this->getRandomSpeciesToPick(Species::getSpecies(), $alreadyAnsweredSpecies, [$speciesA, ...$alreadyBSpeciesFaced]);

        return [
            'speciesA' => $speciesA,
            'speciesB' => $speciesB,
        ];
    }

    private function getRandomSpeciesToPick($species, $alreadyAnsweredSpecies, $excludedSpecies)
    {
        $alreadyAnswered3TimesSpecies = array_unique(array_filter($alreadyAnsweredSpecies, function ($speciesValue) use ($alreadyAnsweredSpecies) {
            $count = count(array_filter($alreadyAnsweredSpecies, function($value) use($alreadyAnsweredSpecies, $speciesValue)
            {return $value === $speciesValue;}
            ));

            return $count === 4;
        }));

        $speciesToRandomlyPick = array_filter($species, function ($specieValue) use ($alreadyAnswered3TimesSpecies, $excludedSpecies) {
            return !in_array($specieValue, $alreadyAnswered3TimesSpecies, true) && !in_array($specieValue, $excludedSpecies, true);
        });

        if (empty($speciesToRandomlyPick)) {
            return null;
        }
        return $speciesToRandomlyPick[array_rand($speciesToRandomlyPick)];
    }
}