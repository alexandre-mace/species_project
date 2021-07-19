<?php

namespace App\Controller;

use App\Domain\Species;
use App\Entity\Question;
use App\Entity\Survey;
use App\Form\QuestionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class SurveyController extends AbstractController
{
    #[Route('/survey', name: 'survey')]
    public function index(Request $request, Session $session, EntityManagerInterface $entityManager): Response
    {
        $surveyId = $session->get('surveyId');
        $survey = $entityManager->getRepository(Survey::class)->findOneBy(['uniqid' => $surveyId]);
        if ($survey === null || $survey->getIsOver()) {
            $survey = new Survey();
            $survey->setUniqid(uniqid());
            $session->set('surveyId', $survey->getUniqid());
            $entityManager->persist($survey);
            $entityManager->flush();
        }

        $alreadyAnsweredSpecies = array_reduce(array_map(function (Question $question) {
           return [$question->getSpeciesA(), $question->getSpeciesB()];
        }, $survey->getQuestions()->toArray()), function ($carry, $item) {
            $carry[] = $item[0];
            $carry[] = $item[1];
            return $carry;
        }) ?? [];


//        foreach ($survey->getQuestions() as $question) {
//            $survey->removeQuestion($question);
//            $entityManager->flush();
//        }

        $speciesA = $this->determineSpeciesToPick(Species::getSpecies(), $alreadyAnsweredSpecies, []);

        $alreadyBSpeciesFaced = array_map(function (Question $question) {
            return $question->getSpeciesB();
        }, array_filter($survey->getQuestions()->toArray(), function (Question $question) use ($speciesA) {
            return $question->getSpeciesA() === $speciesA;
        }));

        $speciesB = $this->determineSpeciesToPick(Species::getSpecies(), $alreadyAnsweredSpecies, [$speciesA, ...$alreadyBSpeciesFaced]);

        if ($speciesA === null || $speciesB === null) {
            return $this->redirectToRoute('results', ['uniqId' => $survey->getUniqid()]);
        }

        $form = $this
            ->createForm(QuestionType::class, null, ['speciesA' => $speciesA, 'speciesB' => $speciesB])
            ->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $question = new Question();
            $question->setSurvey($survey);
            $question->setSpeciesA($form->get('speciesA')->getData());
            $question->setSpeciesB($form->get('speciesB')->getData());
            $question->setRate($form->get('rate')->getData());

            $survey->addQuestion($question);
            $survey->setQuestionNumber($survey->getQuestionNumber() + 1);

            $entityManager->persist($question);
            $entityManager->flush();
           return $this->redirectToRoute('survey');
        }


        return $this->render('survey/index.html.twig', [
            'questionNumber' => $survey->getQuestionNumber(),
            'speciesA'       => $speciesA,
            'speciesB'       => $speciesB,
            'form'           => $form->createView(),
        ]);
    }

    private function determineSpeciesToPick($species, $alreadyAnsweredSpecies, $excludedSpecies)
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
