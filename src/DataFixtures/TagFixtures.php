<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TagFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= 10; $i++) {
            $tag = new Tag();
            $tag->setName("tag$i");
            $manager->persist($tag);
            $this->addReference("tag$i", $tag);
        }

        $manager->flush();
    }
}
