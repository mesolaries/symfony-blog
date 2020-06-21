<?php
/**
 * User: Emil
 * Date: 22/06/2020
 * Time: 2:43 am
 */

namespace App\Controller;


use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $categories = $em->getRepository(Category::class)->findAll();

        return $this->render('template_parts/sidebar/_categories.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/category/{slug}", name="category.show", methods="GET")
     *
     * @param Category $category
     *
     * @return Response
     */
    public function show(Category $category)
    {
        $articles = $category->getPublicArticles();

        return $this->render('category/show.html.twig', [
            'category' => $category,
            'articles' => $articles,
        ]);
    }
}