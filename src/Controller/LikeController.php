<?php

namespace App\Controller;


use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Like;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;

class LikeController extends AbstractController
{
    /**
     * @Route("/like/article/{slug}", name="like.article", methods="POST")
     * @IsGranted("ROLE_USER")
     *
     * @param Article                $article
     * @param Request                $request
     * @param EntityManagerInterface $em
     *
     * @return JsonResponse
     */
    public function articleLike(Article $article, Request $request, EntityManagerInterface $em)
    {
        $like = new Like();
        $like->setArticle($article);
        $like->setAuthor($this->getUser());

        $em->persist($like);
        $em->flush();

        $likeCount = $article->getLikes()->count();

        return new JsonResponse(['likeCount' => $likeCount, 'likeId' => $like->getId()]);
    }

    /**
     * @Route("/like/comment/{id}", name="like.comment", methods="POST")
     * @IsGranted("ROLE_USER")
     *
     * @param Comment                $comment
     * @param Request                $request
     * @param EntityManagerInterface $em
     *
     * @return JsonResponse
     */
    public function commentLike(Comment $comment, Request $request, EntityManagerInterface $em)
    {
        $like = new Like();
        $like->setComment($comment);
        $like->setAuthor($this->getUser());

        $em->persist($like);
        $em->flush();

        $likeCount = $comment->getLikes()->count();

        return new JsonResponse(['likeCount' => $likeCount, 'likeId' => $like->getId()]);
    }

    /**
     * @Route("/like/{id}/remove", name="like.remove", methods="POST")
     *
     * @param Like                   $like
     * @param EntityManagerInterface $em
     *
     * @return JsonResponse
     */
    public function unlike(Like $like, EntityManagerInterface $em)
    {
        if ($like->getAuthor() != $this->getUser()) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $em->remove($like);
        $em->flush();

        $entity = $like->getArticle() ?? $like->getComment();
        $likeCount  = $entity->getLikes()->count();

        $data = ['likeCount' => $likeCount];

        if ($like->getComment()) {
            $data['commentId'] = $like->getComment()->getId();
        }

        return new JsonResponse($data);
    }
}