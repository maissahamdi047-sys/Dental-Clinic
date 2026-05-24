<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Appointment;
use App\Entity\ContactMessage;
use App\Entity\Prescription;
use App\Entity\Payment;
use App\Entity\Service;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin', methods: ['GET','POST'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        if (!$request->getSession()->get('is_admin')) {
            return $this->redirectToRoute('app_admin_login');
        }
        $posted = null;
        if ($request->isMethod('POST')) {
            $posted = [
                'site_name' => $request->request->get('site_name'),
                'email' => $request->request->get('email'),
                'phone' => $request->request->get('phone'),
                'address' => $request->request->get('address'),
                'hours' => $request->request->get('hours'),
                'hero_image_url' => $request->request->get('hero_image_url'),
                'logo_url' => $request->request->get('logo_url'),
            ];
            $this->addFlash('success', 'Settings saved');
        }

        $countUsers = $em->getRepository(User::class)->count([]);
        $countAppointments = $em->getRepository(Appointment::class)->count([]);
        $countPending = $em->getRepository(Appointment::class)->count(['status' => 'Pending Approval']);
        $countPayments = $em->getRepository(Payment::class)->count([]);
        $recentAppointments = $em->getRepository(Appointment::class)->findBy([], ['id' => 'DESC'], 5);
        return $this->render('admin/admin.html.twig', [
            'posted' => $posted,
            'countUsers' => $countUsers,
            'countAppointments' => $countAppointments,
            'countPending' => $countPending,
            'countPayments' => $countPayments,
            'recentAppointments' => $recentAppointments,
        ]);
    }

    #[Route('/admin/users', name: 'app_admin_users', methods: ['GET','POST'])]
    public function users(Request $request, EntityManagerInterface $em): Response
    {
        if (!$request->getSession()->get('is_admin')) {
            return $this->redirectToRoute('app_admin_login');
        }
        if ($request->isMethod('POST')) {
            $name = trim((string)$request->request->get('name'));
            $email = trim((string)$request->request->get('email'));
            $role = (string)$request->request->get('role');
            $status = (string)$request->request->get('status');
            if ($name === '' || $email === '') {
                $this->addFlash('error', 'Name and email are required');
            } else {
                $user = new User();
                $user->setName($name);
                $user->setEmail($email);
                $user->setRole($role ?: 'Staff');
                $user->setStatus($status ?: 'Active');
                $em->persist($user);
                $em->flush();
                $this->addFlash('success', 'User added');
            }
        }
        $users = $em->getRepository(User::class)->findBy([], ['createdAt' => 'DESC']);
        return $this->render('admin/users.html.twig', [ 'users' => $users ]);
    }

    #[Route('/admin/users/{id}/delete', name: 'app_admin_users_delete', methods: ['POST'])]
    public function usersDelete(Request $request, EntityManagerInterface $em, int $id): Response
    {
        if (!$request->getSession()->get('is_admin')) {
            return $this->redirectToRoute('app_admin_login');
        }
        $u = $em->getRepository(User::class)->find($id);
        if (!$u) { $this->addFlash('error', 'User not found'); }
        else { $em->remove($u); $em->flush(); $this->addFlash('success', 'User deleted'); }
        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/admin/appointments', name: 'app_admin_appointments', methods: ['GET','POST'])]
    public function appointments(Request $request, EntityManagerInterface $em): Response
    {
        if (!$request->getSession()->get('is_admin')) {
            return $this->redirectToRoute('app_admin_login');
        }
        if ($request->isMethod('POST')) {
            $patient = trim((string)$request->request->get('patient'));
            $service = (string)$request->request->get('service');
            $date = (string)$request->request->get('date');
            $time = (string)$request->request->get('time');
            $doctor = (string)$request->request->get('doctor');
            $status = (string)$request->request->get('status');
            if ($patient === '' || $date === '' || $time === '') {
                $this->addFlash('error', 'Patient, date and time are required');
            } else {
                try {
                    $dt = new \DateTimeImmutable($date.' '.$time);
                    $ap = new Appointment();
                    $ap->setPatient($patient);
                    $ap->setService($service ?: 'General');
                    $ap->setScheduledAt($dt);
                    $ap->setDoctor($doctor ?: 'Any');
                    $ap->setStatus($status ?: 'Scheduled');
                    $em->persist($ap);
                    $em->flush();
                    $this->addFlash('success', 'Appointment created');
                } catch (\Throwable $e) {
                    $this->addFlash('error', 'Invalid date/time');
                }
            }
        }
        $appointments = $em->getRepository(Appointment::class)->findBy([], ['id' => 'DESC']);
        return $this->render('admin/appointments.html.twig', [ 'appointments' => $appointments ]);
    }

    #[Route('/admin/appointments/{id}/delete', name: 'app_admin_appointments_delete', methods: ['POST'])]
    public function appointmentsDelete(Request $request, EntityManagerInterface $em, int $id): Response
    {
        if (!$request->getSession()->get('is_admin')) {
            return $this->redirectToRoute('app_admin_login');
        }
        $ap = $em->getRepository(Appointment::class)->find($id);
        if (!$ap) { $this->addFlash('error', 'Appointment not found'); }
        else { $em->remove($ap); $em->flush(); $this->addFlash('success', 'Appointment deleted'); }
        return $this->redirectToRoute('app_admin_appointments');
    }

    #[Route('/admin/appointments/{id}/status', name: 'app_admin_appointment_status', methods: ['POST'])]
    public function appointmentStatus(Request $request, EntityManagerInterface $em, int $id): Response
    {
        if (!$request->getSession()->get('is_admin')) {
            return $this->redirectToRoute('app_admin_login');
        }
        $status = (string)$request->request->get('status');
        $allowed = ['Accepted','Refused','Cancelled'];
        $ap = $em->getRepository(Appointment::class)->find($id);
        if (!$ap) {
            $this->addFlash('error', 'Appointment not found');
        } elseif (!in_array($status, $allowed, true)) {
            $this->addFlash('error', 'Invalid status');
        } else {
            $ap->setStatus($status);
            $em->flush();
            $this->addFlash('success', 'Appointment '.$status);
        }
        return $this->redirectToRoute('app_admin_appointments');
    }

    #[Route('/admin/documents', name: 'app_admin_documents', methods: ['GET','POST'])]
    public function documents(Request $request, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        if (!$request->getSession()->get('is_admin')) {
            return $this->redirectToRoute('app_admin_login');
        }
        $sent = false;
        if ($request->isMethod('POST')) {
            $email = (string)$request->request->get('patientEmail');
            $file = $request->files->get('prescriptionFile');
            if (!$file || $file->getClientOriginalExtension() !== 'pdf') {
                $this->addFlash('error', 'Please upload a PDF file');
            } elseif ($email === '') {
                $this->addFlash('error', 'Patient email is required');
            } else {
                $dir = $this->getParameter('kernel.project_dir').'/public/uploads/prescriptions';
                if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
                $safeName = uniqid('presc_').'.pdf';
                $file->move($dir, $safeName);
                $p = new Prescription();
                $p->setPatientEmail($email);
                $p->setFilename($safeName);
                $em->persist($p);
                $em->flush();
                try {
                    $message = (new Email())
                        ->to($email)
                        ->subject('Your Prescription')
                        ->text('Please find your prescription attached.')
                        ->attachFromPath($dir.'/'.$safeName, 'prescription.pdf', 'application/pdf');
                    $mailer->send($message);
                } catch (\Throwable $e) {
                    // ignore send failures; null transport by default
                }
                $sent = true;
                $this->addFlash('success', 'Prescription saved and email queued');
            }
        }
        return $this->render('admin/documents.html.twig', [ 'sent' => $sent ]);
    }

    #[Route('/admin/stats', name: 'app_admin_stats', methods: ['GET'])]
    public function stats(Request $request): Response
    {
        if (!$request->getSession()->get('is_admin')) {
            return $this->redirectToRoute('app_admin_login');
        }
        return $this->render('admin/stats.html.twig');
    }

    #[Route('/admin/payments', name: 'app_admin_payments', methods: ['GET','POST'])]
    public function payments(Request $request, EntityManagerInterface $em): Response
    {
        if (!$request->getSession()->get('is_admin')) {
            return $this->redirectToRoute('app_admin_login');
        }
        if ($request->isMethod('POST')) {
            $amount = (string)$request->request->get('amount');
            $currency = (string)$request->request->get('currency');
            $method = (string)$request->request->get('method');
            $status = (string)$request->request->get('status');
            $reference = (string)$request->request->get('reference');
            $appointmentId = (int)($request->request->get('appointment_id') ?: 0);
            if ($amount === '' || !is_numeric($amount)) {
                $this->addFlash('error', 'Amount is required and must be numeric');
            } else {
                $p = new Payment();
                $p->setAmount(number_format((float)$amount, 2, '.', ''));
                $p->setCurrency($currency ?: 'TND');
                $p->setMethod($method ?: 'Cash');
                $p->setStatus($status ?: 'Pending');
                $p->setReference($reference ?: null);
                if ($appointmentId) {
                    $ap = $em->getRepository(Appointment::class)->find($appointmentId);
                    if ($ap) { $p->setAppointment($ap); }
                }
                $em->persist($p);
                $em->flush();
                $this->addFlash('success', 'Payment recorded');
            }
        }
        $payments = $em->getRepository(Payment::class)->findBy([], ['id' => 'DESC']);
        $appointments = $em->getRepository(Appointment::class)->findBy([], ['id' => 'DESC']);
        return $this->render('admin/payments.html.twig', [ 'payments' => $payments, 'appointments' => $appointments ]);
    }

    #[Route('/admin/payments/{id}/delete', name: 'app_admin_payments_delete', methods: ['POST'])]
    public function paymentsDelete(Request $request, EntityManagerInterface $em, int $id): Response
    {
        if (!$request->getSession()->get('is_admin')) {
            return $this->redirectToRoute('app_admin_login');
        }
        $p = $em->getRepository(Payment::class)->find($id);
        if (!$p) { $this->addFlash('error', 'Payment not found'); }
        else { $em->remove($p); $em->flush(); $this->addFlash('success', 'Payment deleted'); }
        return $this->redirectToRoute('app_admin_payments');
    }

    #[Route('/admin/services', name: 'app_admin_services', methods: ['GET','POST'])]
    public function services(Request $request, EntityManagerInterface $em): Response
    {
        if (!$request->getSession()->get('is_admin')) {
            return $this->redirectToRoute('app_admin_login');
        }
        if ($request->isMethod('POST')) {
            $mode = (string)$request->request->get('mode', 'create');
            if ($mode === 'create') {
                $name = (string)$request->request->get('name');
                $slug = (string)$request->request->get('slug');
                $description = (string)$request->request->get('description');
                $price = (string)$request->request->get('price');
                $duration = (int)$request->request->get('duration');
                $category = (string)$request->request->get('category', 'General Dentistry');
                $imageUrl = (string)$request->request->get('imageUrl');
                if ($name === '' || $slug === '') {
                    $this->addFlash('error', 'Name and slug are required');
                } else {
                    $s = new Service();
                    $s->setName($name);
                    $s->setSlug($slug);
                    $s->setDescription($description ?: '');
                    $s->setPrice($price !== '' ? number_format((float)$price, 2, '.', '') : '0.00');
                    $s->setDurationMin($duration ?: 60);
                    $s->setCategory($category ?: 'General Dentistry');
                    $s->setImageUrl($imageUrl ?: null);
                    $em->persist($s);
                    $em->flush();
                    $this->addFlash('success', 'Service created');
                }
            } elseif ($mode === 'update') {
                $id = (int)$request->request->get('id');
                $s = $em->getRepository(Service::class)->find($id);
                if (!$s) { $this->addFlash('error', 'Service not found'); }
                else {
                    $s->setName((string)$request->request->get('name'));
                    $s->setSlug((string)$request->request->get('slug'));
                    $s->setDescription((string)$request->request->get('description'));
                    $s->setPrice(number_format((float)$request->request->get('price'), 2, '.', ''));
                    $s->setDurationMin((int)$request->request->get('duration'));
                    $s->setCategory((string)$request->request->get('category', 'General Dentistry'));
                    $s->setImageUrl((string)$request->request->get('imageUrl'));
                    $em->flush();
                    $this->addFlash('success', 'Service updated');
                }
            }
        }
        $services = $em->getRepository(Service::class)->findBy([], ['id' => 'DESC']);
        return $this->render('admin/services.html.twig', [ 'services' => $services ]);
    }

    #[Route('/admin/services/{id}/delete', name: 'app_admin_services_delete', methods: ['POST'])]
    public function servicesDelete(Request $request, EntityManagerInterface $em, int $id): Response
    {
        if (!$request->getSession()->get('is_admin')) {
            return $this->redirectToRoute('app_admin_login');
        }
        $s = $em->getRepository(Service::class)->find($id);
        if (!$s) { $this->addFlash('error', 'Service not found'); }
        else { $em->remove($s); $em->flush(); $this->addFlash('success', 'Service deleted'); }
        return $this->redirectToRoute('app_admin_services');
    }

    #[Route('/admin/messages', name: 'app_admin_messages', methods: ['GET'])]
    public function messages(Request $request, EntityManagerInterface $em): Response
    {
        if (!$request->getSession()->get('is_admin')) {
            return $this->redirectToRoute('app_admin_login');
        }
        $messages = $em->getRepository(ContactMessage::class)->findBy([], ['createdAt' => 'DESC']);
        return $this->render('admin/messages.html.twig', [ 'messages' => $messages ]);
    }

    #[Route('/admin/messages/{id}/reply', name: 'app_admin_messages_reply', methods: ['POST'])]
    public function messagesReply(Request $request, EntityManagerInterface $em, MailerInterface $mailer, CsrfTokenManagerInterface $csrf, int $id): Response
    {
        if (!$request->getSession()->get('is_admin')) {
            return $this->redirectToRoute('app_admin_login');
        }
        $m = $em->getRepository(ContactMessage::class)->find($id);
        if (!$m) {
            $this->addFlash('error', 'Message not found');
        } else {
            $submittedToken = (string)$request->request->get('_csrf_token');
            if (!$csrf->isTokenValid(new CsrfToken('admin_message_reply_'.$id, $submittedToken))) {
                $this->addFlash('error', 'Invalid CSRF token');
                return $this->redirectToRoute('app_admin_messages');
            }
            $response = (string)$request->request->get('response');
            if ($response !== '') {
                $m->setResponse($response);
                $em->flush();
                try {
                    $email = (new Email())
                        ->to($m->getEmail())
                        ->subject('Re: '.$m->getSubject())
                        ->text($response);
                    $mailer->send($email);
                } catch (\Throwable $e) {
                    // ignore send failures (null transport in dev)
                }
                $this->addFlash('success', 'Reply saved and email queued');
            } else {
                $this->addFlash('error', 'Response cannot be empty');
            }
        }
        return $this->redirectToRoute('app_admin_messages');
    }

    #[Route('/admin/messages/{id}/delete', name: 'app_admin_messages_delete', methods: ['POST'])]
    public function messagesDelete(Request $request, EntityManagerInterface $em, CsrfTokenManagerInterface $csrf, int $id): Response
    {
        if (!$request->getSession()->get('is_admin')) {
            return $this->redirectToRoute('app_admin_login');
        }
        $m = $em->getRepository(ContactMessage::class)->find($id);
        if (!$m) {
            $this->addFlash('error', 'Message not found');
        } else {
            $submittedToken = (string)$request->request->get('_csrf_token');
            if (!$csrf->isTokenValid(new CsrfToken('admin_message_delete_'.$id, $submittedToken))) {
                $this->addFlash('error', 'Invalid CSRF token');
                return $this->redirectToRoute('app_admin_messages');
            }
            $em->remove($m);
            $em->flush();
            $this->addFlash('success', 'Message deleted');
        }
        return $this->redirectToRoute('app_admin_messages');
    }
}
