<?php

namespace App\Controller;


use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $tags = $em->getRepository(Tag::class)->findAll();

        return $this->render('template_parts/sidebar/_tags.html.twig', [
            'tags' => $tags,
        ]);
    }

    /**
     * @Route("/tag/{slug}", name="tag.show", methods="GET")
     *
     * @param Tag $tag
     *
     * @return Response
     */
    public function show(Tag $tag)
    {
        $articles = $tag->getPublicArticles();

        return $this->render('tag/show.html.twig', [
            'tag' => $tag,
            'articles' => $articles,
        ]);
    }
}