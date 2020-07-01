<?php

namespace App\Form;

use App\Entity\User;
use App\Transformer\StringToFileTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    private DataTransformerInterface $fileTransformer;
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(StringToFileTransformer $fileTransformer, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->fileTransformer = $fileTransformer;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->authorizationChecker->isGranted("ROLE_ADMIN")) {
            $builder->add('roles', ChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'choices' => User::ROLES,
            ]);
        }

        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255, 'min' => 4])
                ]
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('picture', FileType::class, [
                'required' => false,
                'constraints' => [
                    new Image(),
                ]
            ])
        ;

        $builder->get('picture')
            ->addModelTransformer($this->fileTransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
