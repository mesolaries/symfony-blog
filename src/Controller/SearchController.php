<?php

namespace App\Controller;


use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
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
     * @param PaginatorInterface     $paginator
     *
     * @return Response
     */
    public function search(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $q = trim($request->query->get('q'));
        $articles = $paginator->paginate(
            $em->getRepository(Article::class)->findPublicArticlesBySearchQuery($q),
            $request->query->getInt('page', 1),
            $this->getParameter('max_items_per_page'),
            [
                'defaultSortFieldName' => 'a.publishedAt',
                'defaultSortDirection' => 'DESC',
            ]
        );

        return $this->render('search/search.html.twig', [
            'query' => $q,
            'articles' => $articles,
        ]);
    }
}