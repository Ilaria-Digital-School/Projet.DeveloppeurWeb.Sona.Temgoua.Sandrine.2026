<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Cathegory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('summary')
            ->add('content')
            /*  ->add('publishedAt', null, [
                'widget' => 'single_text',
            ]) */
            //->add('isVerified')
            ->add('cathegory', EntityType::class, [
                'class' => Cathegory::class,
                'choice_label' => 'name',
            ])

            ->add('transactionType', ChoiceType::class, [
                'label' => 'Type de transaction',
                'choices' => [
                    'Acheter' => 'acheter',
                    'Brocante' => 'brocante',
                    'Louer' => 'louer',
                    'Donner' => 'donner',
                ],
                'placeholder' => 'Choisir une option',
            ])

            ->add('image', FileType::class, [
                'label' => 'Add Image',
                'mapped' => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
