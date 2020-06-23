<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
     * @Route("/article/read/{slug}", name="article.show", methods="GET")
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

    /**
     * @Route("/article/create", name="article.create", methods={"GET", "POST"})
     * @IsGranted("ROLE_USER")
     *
     * @param Request                $request
     * @param EntityManagerInterface $em
     * @param FileUploader           $fileUploader
     *
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $em, FileUploader $fileUploader)
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $picture = $form['picture']->getData();
            if ($picture) {
                $pictureFileName = $fileUploader->upload($picture);
                $article->setPicture($pictureFileName);
            }

            $em->persist($article);
            $em->flush();

            return $this->redirectToRoute('article.preview', [
                'slug' => $article->getSlug(),
            ]);
        }

        return $this->render('article/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/article/preview/{slug}", name="article.preview", methods="GET")
     * @IsGranted("ROLE_USER")
     *
     * @param Article $article
     *
     * @return Response
     */
    public function preview(Article $article)
    {
        return $this->render('article/preview.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * @Route("/article/publish/{id}", name="article.publish", methods="POST", requirements={"id" = "\d+"})
     *
     * @param Article                $article
     * @param EntityManagerInterface $em
     *
     * @return RedirectResponse
     */
    public function publish(Article $article, EntityManagerInterface $em)
    {
        $article->setIsPublic(true);
        $article->setPublishedAt(new \DateTime());
        $em->flush();

        $this->addFlash('success', 'Your post is now public!');

        return $this->redirectToRoute('article.show', [
            'slug' => $article->getSlug()
        ]);
    }
}
