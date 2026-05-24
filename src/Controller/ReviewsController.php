<?php

namespace App\Controller;

use App\Entity\Review;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReviewsController extends AbstractController
{
    #[Route('/reviews', name: 'app_reviews', methods: ['GET','POST'])]
    public function reviews(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $author = (string)$request->request->get('author');
            $email = (string)$request->request->get('email');
            $rating = (int)$request->request->get('rating', 5);
            $comment = (string)$request->request->get('comment');
            if ($author === '' || $comment === '') {
                $this->addFlash('error', 'Please enter your name and comment');
            } else {
                $r = new Review();
                $r->setAuthor($author);
                $r->setEmail($email ?: null);
                $r->setRating($rating < 1 ? 1 : ($rating > 5 ? 5 : $rating));
                $r->setComment($comment);
                $em->persist($r);
                $em->flush();
                $this->addFlash('success', 'Thanks for your review');
            }
        }
        $reviews = $em->getRepository(Review::class)->findBy([], ['createdAt' => 'DESC']);
        return $this->render('reviews/patient_reviews.html.twig', [ 'reviews' => $reviews ]);
    }
}

