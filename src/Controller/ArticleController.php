<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
     * @param PaginatorInterface     $paginator
     * @param Request                $request
     *
     * @return Response
     */
    public function list(EntityManagerInterface $em, PaginatorInterface $paginator, Request $request)
    {
        $articles = $paginator->paginate(
            $em->getRepository(Article::class)->findPublicArticlesQuery(),
            $request->query->getInt('page', 1),
            $this->getParameter('max_items_per_page'),
            [
                'defaultSortFieldName' => 'a.publishedAt',
                'defaultSortDirection' => 'desc',
            ]
        );

        return $this->render('article/list.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * Shows an article by slug
     *
     * @Route("/article/{slug}", name="article.show", methods="GET")
     *
     * @Entity("article", expr="repository.findPublicArticle(slug)")
     *
     * @param Article $article
     *
     * @return Response
     */
    public function show(Article $article)
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }
}
