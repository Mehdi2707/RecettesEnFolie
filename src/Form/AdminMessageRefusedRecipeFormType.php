<?php

namespace App\Form;

use App\Entity\Recipes;
use App\Entity\RecipeStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminMessageRefusedRecipeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message', options: [
                'label' => 'Message',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => '5'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RecipeStatus::class,
        ]);
    }
}
