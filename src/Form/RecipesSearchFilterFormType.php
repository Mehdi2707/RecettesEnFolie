<?php

namespace App\Form;

use App\Entity\Categories;
use App\Entity\DifficultyLevel;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecipesSearchFilterFormType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $categories = $this->entityManager->getRepository(Categories::class)->findBy(['parent' => null]);

        $choices = $this->getCategoryChoices($categories);

        $builder
            ->add('categories', ChoiceType::class, [
                'choices' => $choices,
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('difficulty', EntityType::class, [
                'class' => DifficultyLevel::class,
                'choice_label' => 'name',
                'label' => false,
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('prepTimeMax', IntegerType::class, [
                'label' => 'Temps de préparation maximum (en minutes)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('ingredients', HiddenType::class, [
                'label' => 'Ingrédients',
                'required' => false,
                'attr' => [
                    'class' => 'hidden-ingredients-field'
                ],
            ])
        ;
    }
    private function getCategoryChoices(array $categories)
    {
        $choices = [];

        foreach ($categories as $category) {
            $choices[ucfirst($category->getName())] = $category;

            $childCategories = $this->entityManager->getRepository(Categories::class)->findBy(['parent' => $category->getId()]);

            if (!empty($childCategories)) {
                $choices += $this->getCategoryChoices($childCategories);
            }
        }

        return $choices;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
