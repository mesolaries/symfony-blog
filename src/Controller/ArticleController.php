<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
     * @Route("/article/{slug}/read", name="article.show", methods="GET")
     *
     * @Entity("article", expr="repository.findPublicArticle(slug)")
     *
     * @param Article $article
     *
     * @return Response
     */
    public function show(Article $article)
    {
        $deleteForm = $this->createDeleteForm($article);
        $commentForm = $this->createForm(CommentType::class);
        $replyForm = $commentForm;
        return $this->render('article/show.html.twig', [
            'article' => $article,
            'deleteForm' => $deleteForm->createView(),
            'commentForm' => $commentForm->createView(),
            'replyFormObject' => $replyForm,
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

            $this->addFlash('success', 'New article created.');

            return $this->redirectToRoute('article.preview', [
                'slug' => $article->getSlug(),
            ]);
        }

        return $this->render('article/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/article/{slug}/preview", name="article.preview", methods="GET")
     *
     * @param Article $article
     *
     * @return Response
     */
    public function preview(Article $article)
    {
        if ($article->getAuthor() != $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $deleteForm = $this->createDeleteForm($article);

        return $this->render('article/preview.html.twig', [
            'article' => $article,
            'deleteForm' => $deleteForm->createView(),
        ]);
    }

    /**
     * @Route("/article/{id}/publish", name="article.publish", methods="POST", requirements={"id" = "\d+"})
     *
     * @param Article                $article
     * @param EntityManagerInterface $em
     *
     * @return RedirectResponse
     */
    public function publish(Article $article, EntityManagerInterface $em)
    {
        if ($article->getAuthor() != $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $article->setIsPublic(true);
        $article->setPublishedAt(new \DateTime());
        $em->flush();

        $this->addFlash('success', 'Your post is now public!');

        return $this->redirectToRoute('article.show', [
            'slug' => $article->getSlug()
        ]);
    }

    /**
     * @Route("/article/{slug}/edit", name="article.edit", methods={"GET", "POST"})
     *
     * @param Article                $article
     * @param Request                $request
     * @param FileUploader           $fileUploader
     * @param EntityManagerInterface $em
     *
     * @return RedirectResponse|Response
     */
    public function edit(Article $article, Request $request, FileUploader $fileUploader, EntityManagerInterface $em)
    {
        if ($article->getAuthor() != $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $form = $this->createForm(ArticleType::class, $article);

        $picture = $article->getPicture();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setPicture($picture);
            $picture = $form['picture']->getData();
            if ($picture instanceof UploadedFile) {
                $pictureFileName = $fileUploader->upload($picture);
                $article->setPicture($pictureFileName);
            }

            $em->flush();

            $this->addFlash('success', 'Edited successfully.');

            $redirectRoute = $article->getIsPublic() ? 'article.show' : 'article.preview';

            return $this->redirectToRoute($redirectRoute, [
                'slug' => $article->getSlug(),
            ]);
        }

        return $this->render('article/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/article/{slug}/delete", name="article.delete", methods="DELETE")
     *
     * @param Article                $article
     * @param Request                $request
     * @param EntityManagerInterface $em
     *
     * @return RedirectResponse
     */
    public function delete(Article $article, Request $request, EntityManagerInterface $em)
    {
        if ($article->getAuthor() != $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $form = $this->createDeleteForm($article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->remove($article);
            $em->flush();
            $this->addFlash('success', 'Deleted successfully.');
        }

        return $this->redirectToRoute('article.list');
    }

    /**
     * @param Article $article
     *
     * @return FormInterface
     */
    public function createDeleteForm(Article $article)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('article.delete', ['slug' => $article->getSlug()]))
            ->setMethod('DELETE')
            ->getForm();
    }
}
