<?php

namespace App\Form;

use App\Entity\DifficultyLevel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DifficultyFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', options: [
                'label' => false,
                'attr' => [
                    'class' => 'form-control mt-3',
                    'placeholder' => 'facile'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DifficultyLevel::class,
        ]);
    }
}
