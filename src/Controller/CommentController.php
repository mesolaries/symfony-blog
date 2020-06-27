<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    /**
     * @Route("/comment/{slug}/add", name="comment.add", methods="POST")
     * @IsGranted("ROLE_USER")
     *
     * @param Article                $article
     * @param Request                $request
     * @param EntityManagerInterface $em
     *
     * @return RedirectResponse
     */
    public function add(Article $article, Request $request, EntityManagerInterface $em)
    {
        $comment = new Comment();
        $comment->setArticle($article);
        $comment->setAuthor($this->getUser());

        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($comment);
            $em->flush();

            $this->addFlash('success', 'Commented successfully.');
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/comment/{id}/reply", name="comment.reply", methods="POST", requirements={"id" = "\d+"})
     * @IsGranted("ROLE_USER")
     *
     * @param Comment                $comment
     * @param Request                $request
     * @param EntityManagerInterface $em
     *
     * @return RedirectResponse
     */
    public function reply(Comment $comment, Request $request, EntityManagerInterface $em)
    {
        $reply = new Comment();
        $reply->setParent($comment);
        $reply->setArticle($comment->getArticle());
        $reply->setAuthor($this->getUser());

        $form = $this->createForm(CommentType::class, $reply);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isSubmitted()) {
            $em->persist($reply);
            $em->flush();

            $this->addFlash('success', 'You replied to the comment.');
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/comment/{id}/delete", name="comment.delete", methods="DELETE", requirements={"id" = "\d+"})
     *
     * @param Comment                $comment
     * @param Request                $request
     * @param EntityManagerInterface $em
     *
     * @return RedirectResponse
     */
    public function delete(Comment $comment, Request $request, EntityManagerInterface $em)
    {
        if ($comment->getAuthor() != $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        if ($this->isCsrfTokenValid($comment->getId() . 'delete-comment', $request->request->get('token'))) {
            $em->remove($comment);
            $em->flush();
            $this->addFlash('success', 'Comment deleted.');
        }

        return $this->redirect($request->headers->get('referer'));
    }
}
