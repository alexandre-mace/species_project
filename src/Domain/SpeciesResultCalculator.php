<?php


namespace App\Domain;


use App\Entity\Survey;

class SpeciesResultCalculator
{
    public function calculateResults(Survey $survey)
    {
        $results = [];
        foreach ($survey->getQuestions() as $question) {
            $results[$question->getSpeciesA()]['values'][] = 5 - $question->getRate();
            $results[$question->getSpeciesB()]['values'][] = $question->getRate() - 5;
        }

        foreach ($results as $key => $result) {
            $results[$key] = round(array_sum($result['values'])/count($result['values']), 2) + 5;
        }

        return $results;
    }
}