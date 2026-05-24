<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DoctorsController extends AbstractController
{
    #[Route('/doctors', name: 'app_doctors')]
    public function index(): Response
    {
        return $this->render('doctors/our_doctors.html.twig', [
            'controller_name' => 'DoctorsController',
        ]);
    }
}
