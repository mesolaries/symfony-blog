<?php

namespace App\Controller;


use App\Entity\Article;
use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

        $deleteForm = $this->createDeleteForm($category);

        return $this->render('category/show.html.twig', [
            'category' => $category,
            'articles' => $articles,
            'deleteForm' => $deleteForm->createView(),
        ]);
    }

    /**
     * @Route("/admin/category/create", name="category.create", methods={"GET", "POST"})
     * @IsGranted("ROLE_ADMIN")
     *
     * @param Request                $request
     * @param EntityManagerInterface $em
     *
     * @return RedirectResponse|Response
     */
    public function create(Request $request, EntityManagerInterface $em)
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($category);
            $em->flush();

            $this->addFlash('success', 'Category created.');

            return $this->redirectToRoute('article.list');
        }

        return $this->render('category/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/category/{slug}/edit", name="category.edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_ADMIN")
     *
     * @param Category               $category
     * @param Request                $request
     * @param EntityManagerInterface $em
     *
     * @return RedirectResponse|Response
     */
    public function edit(Category $category, Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Category edited.');

            return $this->redirectToRoute('category.show', ['slug' => $category->getSlug()]);
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/category/{slug}/delete", name="category.delete", methods="DELETE")
     * @IsGranted("ROLE_ADMIN")
     *
     * @param Category               $category
     * @param Request                $request
     * @param EntityManagerInterface $em
     *
     * @return RedirectResponse
     */
    public function delete(Category $category, Request $request, EntityManagerInterface $em)
    {
        $form = $this->createDeleteForm($category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->remove($category);
            $em->flush();
            $this->addFlash('success', 'Category deleted.');
        }

        return $this->redirectToRoute('article.list');
    }


    /**
     * @param Category $category
     *
     * @return FormInterface
     */
    public function createDeleteForm(Category $category)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('category.delete', ['slug' => $category->getSlug()]))
            ->setMethod('DELETE')
            ->getForm();
    }
}