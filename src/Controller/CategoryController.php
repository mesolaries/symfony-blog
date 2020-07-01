<?php

namespace App\Controller;


use App\Entity\Article;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    /**
     * @param EntityManagerInterface $em
     *
     * @return Response
     */
    public function list(EntityManagerInterface $em)
    {
        $categories = $em->getRepository(Category::class)->findBy([], ['name' => 'ASC']);

        return $this->render('template_parts/sidebar/_categories.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/category/{slug}", name="category.show", methods="GET")
     *
     * @param Category           $category
     * @param PaginatorInterface $paginator
     * @param Request            $request
     *
     * @return Response
     */
    public function show(Category $category, PaginatorInterface $paginator, Request $request)
    {
        $articles = $paginator->paginate(
            $this
                ->getDoctrine()
                ->getRepository(Article::class)
                ->findPublicArticlesByCategoryQuery($category),
            $request->query->getInt('page', 1),
            $this->getParameter('max_items_per_page'),
            [
                'defaultSortFieldName' => 'a.publishedAt',
                'defaultSortDirection' => 'desc',
            ]
        );

        return $this->render('category/show.html.twig', [
            'category' => $category,
            'articles' => $articles,
        ]);
    }
}