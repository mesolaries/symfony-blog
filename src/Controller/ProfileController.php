<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\UserType;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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

    /**
     * @Route("/profile/{username}/edit", name="profile.edit", methods={"GET", "POST"})
     *
     * @param User                   $user
     * @param Request                $request
     * @param FileUploader           $fileUploader
     * @param EntityManagerInterface $em
     *
     * @return Response
     */
    public function edit(User $user, Request $request, FileUploader $fileUploader, EntityManagerInterface $em)
    {
        if ($user != $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $form = $this->createForm(UserType::class, $user);

        $picture = $user->getPicture();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPicture($picture);

            if ((int)$request->get('_deletePicture')) {
                $fileUploader->remove($user->getPicture());
                $user->setPicture(null);
            }

            $picture = $form['picture']->getData();
            if ($picture instanceof UploadedFile) {
                $pictureFilename = $fileUploader->upload($picture);
                $user->setPicture($pictureFilename);
            }

            $em->flush();

            $this->addFlash('success', 'Profile updated.');

            return $this->redirectToRoute('profile', ['username' => $user->getUsername()]);
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }


    /**
     * @Route("/profile/{username}/password", name="profile.password", methods={"GET", "POST"})
     *
     * @param User                         $user
     * @param Request                      $request
     * @param EntityManagerInterface       $em
     * @param UserPasswordEncoderInterface $passwordEncoder
     *
     * @return Response
     */
    public function changePassword(User $user, Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        if ($user != $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Unable to access this page!');
        }

        $form = $this->createForm(ChangePasswordType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $em->flush();

            $this->addFlash('success', 'Password changed.');

            return $this->redirectToRoute('profile.edit', [
                'username' => $user->getUsername()
            ]);
        }

        return $this->render('profile/change_password.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
