<?php
// src/Form/ArticleType.php

namespace App\Form;


use App\Entity\Article;
use App\Entity\Cathegory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotNull;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'article',
                'attr' => [
                    'class' => 'form-input',
                    'placeholder' => 'Entrez un titre accrocheur...'
                ]
            ])

            ->add('summary', TextareaType::class, [
                'label' => 'Résumé',
                'required' => false,
                'attr' => [
                    'class' => 'form-textarea',
                    'rows' => 3,
                    'placeholder' => 'Un bref résumé de votre article...'
                ]
            ])

            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => [
                    'class' => 'form-textarea rich-editor',
                    'rows' => 15,
                    'placeholder' => 'Rédigez votre article ici...'
                ]
            ])
            ->add('cathegory', EntityType::class, [
                'class' => Cathegory::class,
                'choice_label' => 'name',
                'label' => 'Catégorie',
                'required' => true,
                'placeholder' => 'Choisissez une catégorie',
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('transactionType', ChoiceType::class, [
                'label' => 'Type de transaction',
                'required' => true,
                'placeholder' => 'Choisissez un type de transaction',
                'choices' => [
                    'Acheter' => 'acheter',
                    'Louer' => 'louer',
                    'Brocante' => 'brocante',
                    'Don' => 'don',
                    'Produits alimentaires' => 'alimentaire',
                ],
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('image', FileType::class, [
    'label' => 'Image principale',
    'mapped' => false,
    'required' => true,
    'constraints' => [
       // new NotNull([
         //   'message' => 'Veuillez ajouter une image principale.'
       // ]),
        new Image([
            'maxSize' => '5M',
            'mimeTypes' => [
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/avif'
            ],
            'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG, WEBP, AVIF)'
        ])
    ],
    'attr' => [
        'class' => 'form-file',
        'accept' => 'image/*'
    ]
])
            ->add('images', FileType::class, [
                'label' => 'Images supplémentaires',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'constraints' => [
                    new All([
                        'constraints' => [
                            new Image([
                                'maxSize' => '5M',
                                'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp', 'image/avif'],
                                'mimeTypesMessage' => 'Veuillez uploader des images valides (JPEG, PNG, WEBP, AVIF)'
                            ])
                        ]
                    ])
                ],
                'attr' => [
                    'class' => 'form-file',
                    'accept' => 'image/*',
                    'multiple' => 'multiple'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
            'csrf_protection' => true
        ]);
    }
}
