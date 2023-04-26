<?php

namespace App\Form;

use App\Entity\Recipes;
use App\Entity\Users;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecipesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('preparationTime', NumberType::class, [
                'label' => 'Temps de préparation',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('cookingTime', NumberType::class, [
                'label' => 'Temps de cuisson',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('numberOfServings', NumberType::class, [
                'label' => 'Nombre de portions',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('difficultyLevel', TextType::class, [
                'label' => 'Niveau de difficulté',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('user', EntityType::class, [
                'class' => Users::class,
                'choice_label' => 'username',
                'label' => 'Utilisateur',
                'attr' => [
                    'class' => 'mb-3'
                ]
            ])
            ->add('images', FileType::class, [
                'label' => false,
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recipes::class,
        ]);
    }
}
