<?php

namespace App\Form;

use App\Entity\DifficultyLevel;
use App\Entity\Recipes;
use App\Entity\Users;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Image;

class AdminRecipesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', options: [
                'label' => 'Titre',
                'attr' => [
                    'class' => 'form-control'
                ],
            ])
            ->add('description', options: [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('preparationTime', options: [
                'label' => 'Temps de préparation',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('cookingTime', options: [
                'label' => 'Temps de cuisson',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('numberOfServings', options: [
                'label' => 'Nombre de portions',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('difficultyLevel', EntityType::class, [
                'class' => DifficultyLevel::class,
                'choice_label' => 'name',
                'label' => 'Difficulté',
                'attr' => [
                    'class' => 'mb-3'
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
                ],
                'constraints' => [
                    new All(
                        new Image([
                            'maxWidth' => 1280,
                            'maxWidthMessage' => 'L\'image doit faire {{ max_width }} pixels de large au maximum'
                        ])
                    )
                ]
            ])
            ->add('ingredients', CollectionType::class, [
                'entry_type' => IngredientsFormType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
            ])
            ->add('steps', CollectionType::class, [
                'entry_type' => StepsFormType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
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
