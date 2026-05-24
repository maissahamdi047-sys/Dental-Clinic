<?php

namespace App\Controller;

use App\Entity\Patient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

final class SignupController extends AbstractController
{
    #[Route('/signup', name: 'app_signup', methods: ['GET'])]
    public function show(): Response
    {
        return $this->render('signup/signup.html.twig');
    }

    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $em, MailerInterface $mailer, CsrfTokenManagerInterface $csrf): Response
    {
        $submittedToken = (string)$request->request->get('_csrf_token');
        if (!$csrf->isTokenValid(new CsrfToken('register', $submittedToken))) {
            $this->addFlash('error', 'Invalid CSRF token');
            return $this->redirectToRoute('app_signup');
        }
        $fn = (string)$request->request->get('firstName');
        $ln = (string)$request->request->get('lastName');
        $email = (string)$request->request->get('email');
        $phone = (string)$request->request->get('phone');
        $dob = (string)$request->request->get('dob');
        $pw = (string)$request->request->get('password');
        $cpw = (string)$request->request->get('confirmPassword');
        if ($fn === '' || $ln === '' || $email === '' || $pw === '' || $cpw === '') {
            $this->addFlash('error', 'All fields are required');
            return $this->redirectToRoute('app_signup');
        }
        if ($pw !== $cpw) {
            $this->addFlash('error', 'Passwords do not match');
            return $this->redirectToRoute('app_signup');
        }
        $exists = $em->getRepository(Patient::class)->findOneBy(['email' => $email]);
        if ($exists) {
            $this->addFlash('error', 'Email already registered');
            return $this->redirectToRoute('app_signup');
        }
        $p = new Patient();
        $p->setFirstName($fn);
        $p->setLastName($ln);
        $p->setEmail($email);
        $p->setPhone($phone ?: '');
        try {
            $dobDate = new \DateTime($dob);
            $now = new \DateTime();
            $age = $now->diff($dobDate)->y;
            if ($dobDate > $now) {
                 $this->addFlash('error', 'La date de naissance ne peut pas être dans le futur');
                 return $this->redirectToRoute('app_signup');
            }
            if ($age < 18) {
                $this->addFlash('error', 'Vous devez avoir au moins 18 ans pour vous inscrire');
                return $this->redirectToRoute('app_signup');
            }
            if ($age > 120) {
                $this->addFlash('error', 'La date de naissance est invalide');
                return $this->redirectToRoute('app_signup');
            }
            $p->setDob($dobDate);
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Invalid date of birth');
            return $this->redirectToRoute('app_signup');
        }
        $p->setPasswordHash(password_hash($pw, PASSWORD_BCRYPT));
        $token = bin2hex(random_bytes(32));
        $p->setVerificationToken($token);
        $p->setEmailVerified(false);
        $em->persist($p);
        $em->flush();
        $verifyUrl = $this->generateUrl('app_verify_email', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
        $emailMsg = (new Email())
            ->from('no-reply@medident.test')
            ->to($email)
            ->subject('Verify your MediDent account')
            ->html('
                <div style="font-family: Arial, sans-serif; padding: 20px; color: #333;">
                    <h2 style="color: #1a73a7;">Bienvenue chez MediDent !</h2>
                    <p>Merci de vous être inscrit. Veuillez confirmer votre adresse email pour activer votre compte.</p>
                    <p style="margin: 25px 0;">
                        <a href="'.$verifyUrl.'" style="background-color: #2dbdb6; color: white; padding: 12px 25px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">Vérifier mon email</a>
                    </p>
                    <p style="font-size: 12px; color: #777;">Si le bouton ne fonctionne pas, copiez ce lien : <br>'.$verifyUrl.'</p>
                </div>
            ');
        $mailer->send($emailMsg);
        $this->addFlash('success', 'Compte créé. Vérifiez votre email pour activer le compte.');
        return $this->redirectToRoute('app_login');
    }

    #[Route('/verify/{token}', name: 'app_verify_email', methods: ['GET'])]
    public function verify(string $token, EntityManagerInterface $em): Response
    {
        $p = $em->getRepository(Patient::class)->findOneBy(['verificationToken' => $token]);
        if (!$p) {
            $this->addFlash('error', 'Lien de vérification invalide');
            return $this->redirectToRoute('app_login');
        }
        $p->setEmailVerified(true);
        $p->setVerificationToken(null);
        $em->flush();
        $this->addFlash('success', 'Email vérifié. Vous pouvez vous connecter.');
        return $this->redirectToRoute('app_login');
    }
}
