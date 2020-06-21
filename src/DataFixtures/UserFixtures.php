<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private UserPasswordEncoderInterface $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setName('Arya Stark');
        $user->setEmail('aryastark@gmail.com');
        $user->setRoles(['ROLE_USER']);
        $user->setUsername('aryastark');
        $user->setCreatedAt(new \DateTime());
        $user->setPassword($this->encoder->encodePassword($user, 'aryastark'));

        $manager->persist($user);

        $manager->flush();

        $this->addReference('user', $user);
    }
}
