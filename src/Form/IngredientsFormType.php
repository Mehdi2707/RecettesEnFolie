<?php

namespace App\Form;

use App\Entity\Ingredients;
use App\Entity\Unit;
use App\Repository\UnitRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IngredientsFormType extends AbstractType
{
    private $unitRepository;

    public function __construct(UnitRepository $unitRepository)
    {
        $this->unitRepository = $unitRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity', options: [
                'label' => 'Quantité',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('name', TextType::class, [
                'attr' => ['class' => 'ingredient-name-input form-control'],
                'label' => 'Ingrédient'
            ])
            ->add('unit', EntityType::class, [
                'class' => Unit::class,
                'label' => 'Unité',
                'attr' => ['class' => 'custom-select'],
                'choice_label' => 'name',
                'placeholder' => 'Choisissez une unité',
                'required' => false,
                'choices' => $this->unitRepository->findAll(),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ingredients::class,
        ]);
    }
}
