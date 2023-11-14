<?php
namespace App\Controller;

use App\Entity\BlogPost;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    #[Route('/blogpost/{slug}', name: 'blogpost')]
    public function show(
        BlogPost $blogPost,
    ): Response {
        return $this->render('blog/show.html.twig', [
            'blogpost' => $blogPost,
        ]);
    }
}
