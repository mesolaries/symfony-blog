<?php

namespace App\DataFixtures;

use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ArticleFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $article1 = new Article();
        $article1->setPublishedAt(new \DateTime());
        $article1->setIsPublic(true);
        $article1->setCategory($this->getReference('categoryProgramming'));
        $article1->setAuthor($this->getReference('user'));
        $article1->setTitle('Article about programming');
        $article1->setContent('Programming is good.');
        $article1->setPicture('programming_pic.png');
        $article1->addTag($this->getReference('tag1'));
        $article1->addTag($this->getReference('tag2'));

        $article2 = new Article();
        $article2->setPublishedAt(new \DateTime());
        $article2->setIsPublic(true);
        $article2->setCategory($this->getReference('categoryDesign'));
        $article2->setAuthor($this->getReference('user'));
        $article2->setTitle('Article about design');
        $article2->setContent('Design is good.');
        $article2->setPicture('programming_pic.png');
        $article2->addTag($this->getReference('tag2'));
        $article2->addTag($this->getReference('tag3'));
        $article2->addTag($this->getReference('tag4'));

        $article3 = new Article();
        $article3->setPublishedAt(new \DateTime());
        $article3->setIsPublic(true);
        $article3->setCategory($this->getReference('categoryDesign'));
        $article3->setAuthor($this->getReference('user'));
        $article3->setTitle('Article about design');
        $article3->setContent('Design is good.');
        $article3->setPicture('programming_pic.png');
        $article3->addTag($this->getReference('tag3'));
        $article3->addTag($this->getReference('tag4'));
        $article3->addTag($this->getReference('tag5'));

        $manager->persist($article1);
        $manager->persist($article2);
        $manager->persist($article3);

        for ($i = 1; $i < 25; $i++) {
            $article = new Article();
            $article->setPublishedAt(new \DateTime());
            $article->setIsPublic(true);
            $article->setCategory($this->getReference('categoryProgramming'));
            $article->setAuthor($this->getReference('user'));
            $article->setTitle("Article about programming $i");
            $article->setContent("Programming is good $i.");
            $article->setPicture('programming_pic.png');
            $article->addTag($this->getReference('tag3'));
            $article->addTag($this->getReference('tag4'));
            $article->addTag($this->getReference('tag5'));
            $manager->persist($article);
        }

        for ($i = 1; $i < 25; $i++) {
            $article = new Article();
            $article->setPublishedAt(new \DateTime());
            $article->setIsPublic(true);
            $article->setCategory($this->getReference('categoryLifestyle'));
            $article->setAuthor($this->getReference('user'));
            $article->setTitle("Article about lifestyle $i");
            $article->setContent("Lifestyle is good $i.");
            $article->setPicture('programming_pic.png');
            $article->addTag($this->getReference('tag4'));
            $article->addTag($this->getReference('tag5'));
            $article->addTag($this->getReference('tag6'));
            $manager->persist($article);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
          CategoryFixtures::class,
          TagFixtures::class,
          UserFixtures::class,
        ];
    }
}
