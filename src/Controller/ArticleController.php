<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    /**
     * Lists all published articles
     *
     * @Route("/", name="article.list", methods="GET")
     *
     * @param EntityManagerInterface $em
     *
     * @return Response
     */
    public function list(EntityManagerInterface $em)
    {
        $articles = $em->getRepository(Article::class)->findWithPublicArticles();

        return $this->render('article/list.html.twig', [
            'articles' => $articles,
        ]);
    }
}
