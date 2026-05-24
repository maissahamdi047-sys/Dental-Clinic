<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Payment;
use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class StripeController extends AbstractController
{
    #[Route('/stripe/checkout/{id}', name: 'app_stripe_checkout', methods: ['GET'])]
    public function checkout(EntityManagerInterface $em, int $id): Response
    {
        $ap = $em->getRepository(Appointment::class)->find($id);
        if (!$ap) {
            $this->addFlash('error', 'Appointment not found');
            return $this->redirectToRoute('app_appointment');
        }

        $secret = $_ENV['STRIPE_SECRET_KEY'] ?? null;
        if (!$secret) {
            $this->addFlash('error', 'Stripe secret key missing. Set STRIPE_SECRET_KEY in .env.local');
            return $this->redirectToRoute('app_appointment');
        }

        $serviceRepo = $em->getRepository(Service::class);
        $service = $serviceRepo->findOneBy(['slug' => $ap->getService()]) ?: $serviceRepo->findOneBy(['name' => $ap->getService()]);
        $fallbackPrices = [
            'general' => 5000,
            'cleaning' => 7500,
            'whitening' => 15000,
            'braces' => 10000,
            'implants' => 20000,
            'emergency' => 8000,
        ];
        $currency = 'usd';
        $amountCents = 5000;
        if ($service && is_numeric($service->getPrice())) {
            $amountCents = (int)round(((float)$service->getPrice()) * 100);
        } elseif (isset($fallbackPrices[$ap->getService()])) {
            $amountCents = $fallbackPrices[$ap->getService()];
        }

        $successUrl = $this->generateUrl('app_stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $cancelUrl = $this->generateUrl('app_stripe_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL);

        \Stripe\Stripe::setApiKey($secret);
        $session = \Stripe\Checkout\Session::create([
            'mode' => 'payment',
            'success_url' => $successUrl.'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl,
            'client_reference_id' => (string)$ap->getId(),
            'line_items' => [[
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => 'Dental Appointment - '.$ap->getService(),
                        'metadata' => ['appointment_id' => (string)$ap->getId()],
                    ],
                    'unit_amount' => $amountCents,
                ],
                'quantity' => 1,
            ]],
        ]);

        return new RedirectResponse($session->url);
    }

    #[Route('/stripe/success', name: 'app_stripe_success', methods: ['GET'])]
    public function success(Request $request, EntityManagerInterface $em): Response
    {
        $secret = $_ENV['STRIPE_SECRET_KEY'] ?? null;
        $sessionId = (string)$request->query->get('session_id');
        if (!$secret || !$sessionId) {
            $this->addFlash('error', 'Stripe session missing');
            return $this->redirectToRoute('app_appointment');
        }
        \Stripe\Stripe::setApiKey($secret);
        $session = \Stripe\Checkout\Session::retrieve($sessionId);
        if ($session && $session->payment_status === 'paid') {
            $appointmentId = (int)($session->client_reference_id ?: 0);
            $ap = $appointmentId ? $em->getRepository(Appointment::class)->find($appointmentId) : null;
            if ($ap) {
                $ap->setStatus('Pending Approval');
                $p = new Payment();
                $p->setAppointment($ap);
                $p->setAmount(number_format(((float)$session->amount_total)/100.0, 2, '.', ''));
                $p->setCurrency(strtoupper($session->currency ?: 'USD'));
                $p->setMethod('Online');
                $p->setStatus('Paid');
                $p->setReference($session->id);
                $em->persist($p);
                $em->flush();

                $dir = $this->getParameter('kernel.project_dir').DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'invoices';
                if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
                $html = $this->renderView('payments/invoice.html.twig', [ 'payment' => $p ]);
                $options = new \Dompdf\Options();
                $options->set('isRemoteEnabled', true);
                $options->set('defaultFont', 'DejaVu Sans');
                $options->setChroot($this->getParameter('kernel.project_dir').DIRECTORY_SEPARATOR.'public');
                $dompdf = new \Dompdf\Dompdf($options);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $pdfOutput = $dompdf->output();
                $file = $dir.DIRECTORY_SEPARATOR.'invoice-'.$p->getId().'.pdf';
                @file_put_contents($file, $pdfOutput);

                return $this->redirect('/uploads/invoices/'.'invoice-'.$p->getId().'.pdf');
            }
            $this->addFlash('success', 'Payment completed. Awaiting admin approval.');
        } else {
            $this->addFlash('error', 'Payment not completed');
        }
        return $this->redirectToRoute('app_appointment');
    }

    #[Route('/stripe/cancel', name: 'app_stripe_cancel', methods: ['GET'])]
    public function cancel(): Response
    {
        $this->addFlash('error', 'Stripe payment cancelled');
        return $this->redirectToRoute('app_appointment');
    }
}
