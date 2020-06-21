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
        $article1->setCreatedAt(new \DateTime());
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
        $article2->setCreatedAt(new \DateTime());
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

        $manager->persist($article1);
        $manager->persist($article2);

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
