<?php

namespace App\Controller;

use App\Entity\Payment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class InvoiceController extends AbstractController
{
    #[Route('/invoice/{id}', name: 'app_invoice', methods: ['GET'])]
    public function view(EntityManagerInterface $em, int $id): Response
    {
        $p = $em->getRepository(Payment::class)->find($id);
        if (!$p) {
            $this->addFlash('error', 'Invoice not found');
            return $this->redirectToRoute('app_home');
        }
        return $this->render('payments/invoice.html.twig', [ 'payment' => $p ]);
    }
}

