<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminAuthController extends AbstractController
{
    #[Route('/admin/login', name: 'app_admin_login', methods: ['GET','POST'])]
    public function login(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $email = (string)$request->request->get('email');
            $password = (string)$request->request->get('password');
            if ($email === 'aymen@medident.com' && $password === 'admin123') {
                $request->getSession()->set('is_admin', true);
                $this->addFlash('success', 'Welcome, admin');
                return $this->redirectToRoute('app_admin');
            }
            $this->addFlash('error', 'Invalid admin credentials');
        }
        return $this->render('admin/login.html.twig');
    }

    #[Route('/admin/logout', name: 'app_admin_logout', methods: ['POST','GET'])]
    public function logout(Request $request): Response
    {
        $request->getSession()->remove('is_admin');
        return $this->redirectToRoute('app_admin_login');
    }
}

