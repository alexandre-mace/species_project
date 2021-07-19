<?php

namespace App\Controller;

use App\Entity\Survey;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $surveys = $entityManager->getRepository(Survey::class)->findAll();

        return $this->render('home/index.html.twig', [
            'total' => count($surveys)
        ]);
    }
}
