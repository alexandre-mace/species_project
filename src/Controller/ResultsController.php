<?php

namespace App\Controller;

use App\Domain\SpeciesResultCalculator;
use App\Entity\Survey;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class ResultsController extends AbstractController
{
    #[Route('/results/{uniqId}', name: 'results')]
    public function index(
        $uniqId,
        EntityManagerInterface $entityManager,
        ChartBuilderInterface $chartBuilder,
        SpeciesResultCalculator $speciesResultCalculator
    ): Response
    {
        $survey = $entityManager->getRepository(Survey::class)->findOneBy(['uniqid' => $uniqId]);
        if ($survey === null) {
            throw new NotFoundHttpException();
        }

        $survey->setIsOver(true);
        $entityManager->flush();

        $results = $speciesResultCalculator->calculateResults($survey);

        $chart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels' => array_keys($results),
            'datasets' => [
                [
                    'label' => 'Species rate',
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(140, 159, 64, 0.2)',
                        'rgba(255, 180, 0, 0.2)',
                    ],
                    'borderColor' => [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(140, 159, 64, 1)',
                        'rgba(255, 180, 0, 1)',
                    ],
                    'borderWidth' => 1,
                    'data' => array_values($results),
                ],
            ],
        ]);

        $chart->setOptions([
            'scales' => [
                'yAxes' => [
                    ['ticks' => ['min' => 0, 'max' => 10]],
                ],
            ],
        ]);

        return $this->render('results/index.html.twig', [
            'chart' => $chart,
        ]);
    }
}
