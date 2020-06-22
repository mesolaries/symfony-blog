<?php

namespace App\Controller;


use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    /**
     * @Route("/search", name="search")
     *
     * @param Request                $request
     * @param EntityManagerInterface $em
     *
     * @return Response
     */
    public function search(Request $request, EntityManagerInterface $em)
    {
        $q = trim($request->query->get('q'));
        $articles = $em->getRepository(Article::class)->findPublicArticlesBySearchQuery($q);

        return $this->render('search/search.html.twig', [
            'query' => $q,
            'articles' => $articles,
        ]);
    }
}