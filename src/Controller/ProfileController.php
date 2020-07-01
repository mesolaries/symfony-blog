<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profile/{username}", name="profile", methods="GET")
     * @IsGranted("ROLE_USER")
     *
     * @param User                   $user
     * @param EntityManagerInterface $em
     * @param PaginatorInterface     $paginator
     * @param Request                $request
     *
     * @return Response
     */
    public function index(User $user, EntityManagerInterface $em, PaginatorInterface $paginator, Request $request)
    {
        $articles = $paginator->paginate(
            $em->getRepository(Article::class)->findArticlesByUserQuery($user),
            $request->query->getInt('page', '1'),
            $this->getParameter('max_items_per_page'),
            [
                'defaultSortFieldName' => 'a.publishedAt',
                'defaultSortDirection' => 'desc',
            ]
        );


        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'articles' => $articles,
        ]);
    }
}
