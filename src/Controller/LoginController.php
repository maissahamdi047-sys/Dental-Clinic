<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Patient;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
 

final class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: ['GET','POST'])]
    public function index(Request $request, EntityManagerInterface $em, CsrfTokenManagerInterface $csrf): Response
    {
        if ($request->isMethod('POST')) {
            $submittedToken = (string)$request->request->get('_csrf_token');
            if (!$csrf->isTokenValid(new CsrfToken('authenticate', $submittedToken))) {
                $this->addFlash('error', 'Invalid CSRF token');
                return $this->render('login/login.html.twig');
            }
            $email = (string)$request->request->get('_username', (string)$request->request->get('email'));
            $password = (string)$request->request->get('_password', (string)$request->request->get('password'));
            $patient = $em->getRepository(Patient::class)->findOneBy(['email' => $email]);
            if (!$patient || !password_verify($password, $patient->getPasswordHash())) {
                $this->addFlash('error', 'Invalid credentials');
                return $this->render('login/login.html.twig');
            }
            $session = $request->getSession();
            $session->set('patient_id', $patient->getId());
            $session->set('patient_email', $patient->getEmail());
            $session->set('patient_name', $patient->getFirstName().' '.$patient->getLastName());
            $this->addFlash('success', 'Welcome back');
            return $this->redirectToRoute('app_patient_dashboard');
        }
        return $this->render('login/login.html.twig');
    }

 
    #[Route('/logout', name: 'app_logout', methods: ['POST','GET'])]
    public function logout(Request $request): Response
    {
        $request->getSession()->invalidate();
        return $this->redirectToRoute('app_login');
    }

    #[Route('/forgot', name: 'app_forgot', methods: ['GET','POST'])]
    public function forgot(Request $request, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        if ($request->isMethod('POST')) {
            $email = (string)$request->request->get('email', '');
            $p = $em->getRepository(Patient::class)->findOneBy(['email' => $email]);
            if ($p) {
                $token = bin2hex(random_bytes(32));
                $p->setResetToken($token);
                $p->setResetTokenExpiresAt((new \DateTime())->add(new \DateInterval('PT1H')));
                $em->flush();
                $resetUrl = $this->generateUrl('app_reset', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
                $msg = (new Email())->from('no-reply@medident.test')->to($email)->subject('Reset your MediDent password')
                    ->html('
                        <div style="font-family: Arial, sans-serif; padding: 20px; color: #333;">
                            <h2 style="color: #1a73a7;">Réinitialisation de mot de passe</h2>
                            <p>Vous avez demandé à réinitialiser votre mot de passe MediDent.</p>
                            <p>Cliquez sur le bouton ci-dessous pour continuer :</p>
                            <p style="margin: 25px 0;">
                                <a href="'.$resetUrl.'" style="background-color: #2dbdb6; color: white; padding: 12px 25px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">Réinitialiser le mot de passe</a>
                            </p>
                            <p style="font-size: 12px; color: #777;">Si le bouton ne fonctionne pas, copiez ce lien : <br>'.$resetUrl.'</p>
                        </div>
                    ');
                try {
                    $mailer->send($msg);
                } catch (TransportExceptionInterface|\Throwable $e) {
                    $this->addFlash('error', 'Échec d’envoi de l’email. Démarrez Mailpit (SMTP : localhost:1025, UI : http://localhost:8025).');
                }
            }
            $this->addFlash('success', 'Si l’email existe, un lien a été envoyé.');
            return $this->redirectToRoute('app_forgot');
        }
        return $this->render('login/forgot.html.twig');
    }

    #[Route('/reset/{token}', name: 'app_reset', methods: ['GET','POST'])]
    public function reset(Request $request, EntityManagerInterface $em, string $token): Response
    {
        $p = $em->getRepository(Patient::class)->findOneBy(['resetToken' => $token]);
        if (!$p || !$p->getResetTokenExpiresAt() || $p->getResetTokenExpiresAt() < new \DateTime()) {
            $this->addFlash('error', 'Lien de réinitialisation invalide ou expiré');
            return $this->redirectToRoute('app_forgot');
        }
        if ($request->isMethod('POST')) {
            $pw = (string)$request->request->get('password', '');
            $cpw = (string)$request->request->get('confirmPassword', '');
            if ($pw === '' || $pw !== $cpw) {
                $this->addFlash('error', 'Le mot de passe ne correspond pas');
                return $this->redirectToRoute('app_reset', ['token' => $token]);
            }
            $p->setPasswordHash(password_hash($pw, PASSWORD_BCRYPT));
            $p->setResetToken(null);
            $p->setResetTokenExpiresAt(null);
            $em->flush();
            $this->addFlash('success', 'Mot de passe mis à jour. Connectez-vous.');
            return $this->redirectToRoute('app_login');
        }
        return $this->render('login/reset.html.twig', ['token' => $token]);
    }
}
