<?php

namespace App\Controller;


use App\Entity\Article;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TagController extends AbstractController
{
    /**
     * @param EntityManagerInterface $em
     *
     * @return Response
     */
    public function list(EntityManagerInterface $em)
    {
        $tags = $em->getRepository(Tag::class)->findTopTenWithPublicArticles();

        return $this->render('template_parts/sidebar/_tags.html.twig', [
            'tags' => $tags,
        ]);
    }

    /**
     * @Route("/tag/{slug}", name="tag.show", methods="GET")
     *
     * @param Tag                $tag
     * @param PaginatorInterface $pagination
     * @param Request            $request
     *
     * @return Response
     */
    public function show(Tag $tag, PaginatorInterface $pagination, Request $request)
    {
        $articles = $pagination->paginate(
            $this
                ->getDoctrine()
                ->getRepository(Article::class)
                ->findPublicArticlesByTagQuery($tag),
            $request->query->getInt('page', 1),
            $this->getParameter('max_items_per_page'),
            [
                'defaultSortFieldName' => 'a.publishedAt',
                'defaultSortDirection' => 'desc',
            ]
        );

        return $this->render('tag/show.html.twig', [
            'tag' => $tag,
            'articles' => $articles,
        ]);
    }
}