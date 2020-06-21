<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $categoryProgramming = new Category();
        $categoryProgramming->setName('Programming');

        $categoryDesign = new Category();
        $categoryDesign->setName('Design');

        $categoryLifestyle = new Category();
        $categoryLifestyle->setName('Lifestyle');

        $manager->persist($categoryProgramming);
        $manager->persist($categoryDesign);
        $manager->persist($categoryLifestyle);

        $manager->flush();

        $this->addReference('categoryProgramming', $categoryProgramming);
        $this->addReference('categoryDesign', $categoryDesign);
        $this->addReference('categoryLifestyle', $categoryLifestyle);
    }
}
