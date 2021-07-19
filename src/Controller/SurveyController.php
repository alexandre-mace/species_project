<?php

namespace App\Controller;

use App\Domain\SpeciesPicker;
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
    public function index(
        Request $request,
        Session $session,
        EntityManagerInterface $entityManager,
        SpeciesPicker $speciesPicker,
    ): Response
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

//        foreach ($survey->getQuestions() as $question) {
//            $survey->removeQuestion($question);
//            $entityManager->flush();
//        }

        $species = $speciesPicker->getSpeciesForQuestion($survey);
        $speciesA = $species['speciesA'];
        $speciesB = $species['speciesB'];

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
}
