<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AppointmentController extends AbstractController
{
    #[Route('/appointment', name: 'app_appointment', methods: ['GET','POST'])]
    public function book(Request $request, EntityManagerInterface $em): Response
    {
        $posted = false;
        if ($request->isMethod('POST')) {
            $service = (string)$request->request->get('service');
            $date = (string)$request->request->get('date');
            $time = (string)$request->request->get('time');
            $doctor = (string)$request->request->get('doctor');
            $first = (string)$request->request->get('firstName');
            $last = (string)$request->request->get('lastName');
            $name = trim(($first.' '.$last)) ?: (string)$request->request->get('full_name', (string)$request->request->get('name'));
            $email = (string)$request->request->get('email');
            $paymentMethod = (string)$request->request->get('payment_method', 'cash');
            if ($name === '' || $date === '' || $time === '') {
                $this->addFlash('error', 'Name, date and time are required');
            } else {
                try {
                    $dt = new \DateTimeImmutable($date.' '.$time);
                    $ap = new Appointment();
                    $ap->setPatient($name);
                    $ap->setService($service ?: 'General');
                    $ap->setScheduledAt($dt);
                    $ap->setDoctor($doctor ?: 'Any');
                    $ap->setPatientEmail($email ?: null);
                    if ($paymentMethod === 'stripe') {
                        $ap->setStatus('Pending Payment');
                        $em->persist($ap);
                        $em->flush();
                        return $this->redirectToRoute('app_stripe_checkout', ['id' => $ap->getId()]);
                    } else {
                        $ap->setStatus('Pending Approval');
                        $em->persist($ap);
                        $em->flush();
                        $this->addFlash('success', 'Appointment request submitted');
                        $posted = true;
                    }
                } catch (\Throwable $e) {
                    $this->addFlash('error', 'Invalid date/time');
                }
            }
        }
        $services = $em->getRepository(Service::class)->findBy([], ['name' => 'ASC']);
        return $this->render('appointment/appointment_booking.html.twig', [ 'posted' => $posted, 'services' => $services ]);
    }
}
