<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Category;
use App\Transformer\StringToFileTransformer;
use App\Transformer\TagToStringTransformer;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ArticleType extends AbstractType
{
    private DataTransformerInterface $transformer;
    private DataTransformerInterface $fileTransformer;

    public function __construct(TagToStringTransformer $transformer, StringToFileTransformer $fileTransformer)
    {
        $this->transformer = $transformer;
        $this->fileTransformer = $fileTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255]),
                ]
            ])
            ->add('content', CKEditorType::class, [
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('picture', FileType::class, [
                'required' => false,
                'constraints' => [
                    new Image(),
                ],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('tag', TextType::class, [
                'required' => false,
            ])
        ;

        $builder->get('tag')
            ->addModelTransformer($this->transformer);

        $builder->get('picture')
            ->addModelTransformer($this->fileTransformer);

        // $builder->setMethod('PATCH');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class
        ]);
    }
}
