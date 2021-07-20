<?php

namespace App\Controller;

use App\Domain\SpeciesResultCalculator;
use App\Entity\Survey;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function index(
        EntityManagerInterface $entityManager,
        SpeciesResultCalculator $speciesResultCalculator,
        ChartBuilderInterface $chartBuilder
    ): Response
    {
        $surveys = $entityManager->getRepository(Survey::class)->findAll();

        $averageResults = [];
        foreach ($surveys as $survey) {
            $results = $speciesResultCalculator->calculateResults($survey);
            foreach ($results as $key => $resultAverage) {
                $averageResults[$key][] = $resultAverage;
            }
        }

        foreach ($averageResults as $key => $result) {
            $averageResults[$key] = round(array_sum($result)/count($result), 2);
        }

        $chart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels' => array_keys($averageResults),
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
                    'data' => array_values($averageResults),
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

        return $this->render('dashboard/index.html.twig', [
            'chart' => $chart,
            'total' => count($surveys)
        ]);
    }
}
