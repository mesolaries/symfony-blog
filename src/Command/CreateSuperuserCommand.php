<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CreateSuperuserCommand extends Command
{
    protected static $defaultName = 'app:create:superuser';

    protected UserPasswordEncoderInterface $encoder;
    protected EntityManagerInterface $em;
    protected ValidatorInterface $validator;

    public function __construct(UserPasswordEncoderInterface $encoder, EntityManagerInterface $em, ValidatorInterface $validator)
    {
        parent::__construct(self::$defaultName);
        $this->encoder = $encoder;
        $this->em = $em;
        $this->validator = $validator;
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates admin user.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');

        $usernameQuestion = new Question("Enter username for admin (defaults to admin): ", "admin");
        $usernameQuestion->setValidator(function ($username) {
            if (!preg_match('/^[a-z0-9_.]+$/', $username)) {
                throw new \RuntimeException(
                    'Username can only contain of lowercase letters, numbers, _ and .'
                );
            } else if ($this->em->getRepository(User::class)->findOneBy(['username' => $username])) {
                throw new \RuntimeException(
                    "User with $username username already exists."
                );
            }

            return $username;
        });

        $nameQuestion = new Question("Enter a name for admin (defaults to Admin): ", "Admin");

        $emailQuestion = new Question("Enter an email address for admin (defaults to admin@example.com): ", "admin@example.com");
        $emailQuestion->setValidator(function ($email) {
            $emailConstraint = new Assert\Email();
            if (0 !== count($this->validator->validate($email, $emailConstraint))) {
                throw new \RuntimeException(
                    'Please, enter a valid email address.'
                );
            } else if ($this->em->getRepository(User::class)->findOneBy(['email' => $email])) {
                throw new \RuntimeException(
                  "User with $email email already exists."
                );
            }

            return $email;
        });

        $passwordQuestion = new Question("Set password for admin: ");
        $passwordQuestion->setHidden(true);
        $passwordQuestion->setTrimmable(false);
        $passwordQuestion->setNormalizer(function ($password) {
           return rtrim($password, "\n");
        });
        $passwordQuestion->setValidator(function ($password) {
           if (mb_strlen($password) < 6 ) {
               throw new \RuntimeException(
                   'Password length must be longer than 5 characters.'
               );
           }

           return $password;
        });

        $username = $helper->ask($input, $output, $usernameQuestion);
        $name = $helper->ask($input, $output, $nameQuestion);
        $email = $helper->ask($input, $output, $emailQuestion);
        $password = $helper->ask($input, $output, $passwordQuestion);

        $user = new User();

        $user
            ->setUsername($username)
            ->setName($name)
            ->setEmail($email)
            ->setPassword($this->encoder->encodePassword($user, $password))
            ->setRoles(['ROLE_ADMIN']);

        $this->em->persist($user);
        $this->em->flush();

        $io->success("Admin user with username <options=bold,underscore>$username</> created!");

        return 0;
    }
}
