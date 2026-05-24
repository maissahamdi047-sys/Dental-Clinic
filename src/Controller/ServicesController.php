<?php

namespace App\Controller;

use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ServicesController extends AbstractController
{
    #[Route('/services', name: 'app_services', methods: ['GET'])]
    public function list(EntityManagerInterface $em): Response
    {
        $services = $em->getRepository(Service::class)->findAll();
        return $this->render('services/services.html.twig', [ 'services' => $services ]);
    }
}

