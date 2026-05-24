<?php

namespace App\Controller;

use App\Entity\ContactMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact', methods: ['GET','POST'])]
    public function contact(Request $request, EntityManagerInterface $em, CsrfTokenManagerInterface $csrf): Response
    {
        if ($request->isMethod('POST')) {
            $submittedToken = (string)$request->request->get('_csrf_token');
            if (!$csrf->isTokenValid(new CsrfToken('contact', $submittedToken))) {
                $this->addFlash('error', 'Invalid CSRF token');
                return $this->render('contact/contact.html.twig');
            }
            $name = trim((string)$request->request->get('name', ''));
            $email = trim((string)$request->request->get('email', ''));
            $subject = trim((string)$request->request->get('subject', ''));
            $message = trim((string)$request->request->get('message', ''));
            if ($email === '' || $message === '') {
                $this->addFlash('error', 'Please fill required fields');
            } else {
                if ($name === '') {
                    $sessionName = (string)$request->getSession()->get('patient_name', '');
                    $name = $sessionName !== '' ? $sessionName : 'Guest';
                }
                $m = new ContactMessage();
                $m->setName($name);
                $m->setEmail($email);
                $m->setSubject($subject !== '' ? $subject : 'Contact');
                $m->setMessage($message);
                $em->persist($m);
                $em->flush();
                $this->addFlash('success', 'Message sent');
            }
        }
        return $this->render('contact/contact.html.twig');
    }
}
