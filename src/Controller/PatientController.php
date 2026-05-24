<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Prescription;
use App\Entity\ContactMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PatientController extends AbstractController
{
    #[Route('/patient', name: 'app_patient_dashboard', methods: ['GET'])]
    public function dashboard(Request $request, EntityManagerInterface $em): Response
    {
        $email = (string)$request->getSession()->get('patient_email', '');
        if ($email === '') {
            return $this->redirectToRoute('app_login');
        }
        $appointments = $em->getRepository(Appointment::class)->findBy(['patientEmail' => $email], ['scheduledAt' => 'ASC']);
        $prescriptions = $em->getRepository(Prescription::class)->findBy(['patientEmail' => $email], ['sentAt' => 'DESC']);
        $messages = $em->getRepository(ContactMessage::class)->findBy(['email' => $email], ['createdAt' => 'DESC']);
        $paymentsRepo = $em->getRepository(\App\Entity\Payment::class);
        $qb = $paymentsRepo->createQueryBuilder('p')
            ->leftJoin('p.appointment', 'a')
            ->addSelect('a')
            ->where('a.patientEmail = :email')
            ->setParameter('email', $email)
            ->orderBy('p.id', 'DESC');
        $payments = $qb->getQuery()->getResult();
        return $this->render('patient/dashboard.html.twig', [
            'appointments' => $appointments,
            'prescriptions' => $prescriptions,
            'payments' => $payments,
            'messages' => $messages,
        ]);
    }

    #[Route('/patient/appointments/{id}/cancel', name: 'app_patient_appointment_cancel', methods: ['POST'])]
    public function cancel(Request $request, EntityManagerInterface $em, int $id): Response
    {
        $email = (string)$request->getSession()->get('patient_email', '');
        $ap = $em->getRepository(Appointment::class)->find($id);
        if (!$ap || $ap->getPatientEmail() !== $email) {
            $this->addFlash('error', 'Appointment not found');
            return $this->redirectToRoute('app_patient_dashboard');
        }
        $ap->setStatus('CancelRequested');
        $em->flush();
        $this->addFlash('success', 'Cancellation requested');
        return $this->redirectToRoute('app_patient_dashboard');
    }

    #[Route('/patient/appointments/{id}/reschedule', name: 'app_patient_appointment_reschedule', methods: ['POST'])]
    public function reschedule(Request $request, EntityManagerInterface $em, int $id): Response
    {
        $email = (string)$request->getSession()->get('patient_email', '');
        $ap = $em->getRepository(Appointment::class)->find($id);
        if (!$ap || $ap->getPatientEmail() !== $email) {
            $this->addFlash('error', 'Appointment not found');
            return $this->redirectToRoute('app_patient_dashboard');
        }
        $date = (string)$request->request->get('date');
        $time = (string)$request->request->get('time');
        $doctor = (string)$request->request->get('doctor');
        try {
            $dt = new \DateTimeImmutable($date.' '.$time);
            $now = new \DateTimeImmutable();
            if ($dt <= $now) {
                $this->addFlash('error', 'La date de rendez-vous doit être dans le futur.');
                return $this->redirectToRoute('app_patient_dashboard');
            }
            $existing = $em->getRepository(Appointment::class)->findOneBy([
                'doctor' => $doctor ?: $ap->getDoctor(),
                'scheduledAt' => $dt,
            ]);
            if ($existing) {
                $this->addFlash('error', 'Ce créneau n’est pas disponible. Veuillez choisir un autre horaire.');
                return $this->redirectToRoute('app_patient_dashboard');
            }
            $ap->setScheduledAt($dt);
            if ($doctor) { $ap->setDoctor($doctor); }
            $ap->setStatus('Pending Approval');
            $em->flush();
            $this->addFlash('success', 'Votre demande de reprogrammation a été envoyée.');
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Date/heure invalide.');
        }
        return $this->redirectToRoute('app_patient_dashboard');
    }

    #[Route('/patient/availability', name: 'app_patient_availability', methods: ['GET'])]
    public function availability(Request $request, EntityManagerInterface $em): Response
    {
        $date = (string)$request->query->get('date');
        $doctor = (string)$request->query->get('doctor', '');
        if ($date === '') {
            return new JsonResponse(['error' => 'date required'], 400);
        }
        try {
            $start = new \DateTimeImmutable($date.' 00:00:00');
            $end = new \DateTimeImmutable($date.' 23:59:59');
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'invalid date'], 400);
        }
        $repo = $em->getRepository(Appointment::class);
        $qb = $repo->createQueryBuilder('a')
            ->where('a.scheduledAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end);
        if ($doctor !== '') {
            $qb->andWhere('a.doctor = :doctor')->setParameter('doctor', $doctor);
        }
        $apps = $qb->getQuery()->getResult();
        $booked = [];
        foreach ($apps as $a) { $booked[] = $a->getScheduledAt()->format('H:i'); }
        $slots = [];
        $open = new \DateTimeImmutable($date.' 09:00');
        $close = new \DateTimeImmutable($date.' 17:00');
        for ($t = $open; $t < $close; $t = $t->modify('+30 minutes')) {
            $h = $t->format('H:i');
            if (!in_array($h, $booked, true)) { $slots[] = $h; }
        }
        return new JsonResponse(['available' => $slots, 'booked' => $booked]);
    }
}
