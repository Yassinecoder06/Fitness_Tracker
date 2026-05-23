<?php

namespace App\Form;

use App\Entity\Meal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class MealType extends AbstractType
{
    public const MEAL_TYPES = ['Breakfast', 'Lunch', 'Snack', 'Dinner', 'Pre-Workout', 'Post-Workout'];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('foodName', TextType::class, [
                'label' => 'Food Name',
                'attr'  => ['placeholder' => 'e.g. Grilled Chicken', 'class' => 'form-control'],
                'constraints' => [new NotBlank()],
            ])
            ->add('mealType', ChoiceType::class, [
                'label'   => 'Meal Type',
                'choices' => array_combine(self::MEAL_TYPES, self::MEAL_TYPES),
                'attr'    => ['class' => 'form-select'],
            ])
            ->add('calories', IntegerType::class, [
                'label' => 'Calories (kcal)',
                'attr'  => ['placeholder' => 'e.g. 450', 'min' => 0, 'class' => 'form-control'],
                'constraints' => [new NotBlank(), new PositiveOrZero()],
            ])
            ->add('protein', IntegerType::class, [
                'label' => 'Protein (g)',
                'attr'  => ['placeholder' => 'Protein', 'min' => 0, 'class' => 'form-control'],
                'constraints' => [new PositiveOrZero()],
                'required' => false,
                'empty_data' => 0,
            ])
            ->add('carbs', IntegerType::class, [
                'label' => 'Carbs (g)',
                'attr'  => ['placeholder' => 'Carbs', 'min' => 0, 'class' => 'form-control'],
                'constraints' => [new PositiveOrZero()],
                'required' => false,
                'empty_data' => 0,
            ])
            ->add('fat', IntegerType::class, [
                'label' => 'Fat (g)',
                'attr'  => ['placeholder' => 'Fat', 'min' => 0, 'class' => 'form-control'],
                'constraints' => [new PositiveOrZero()],
                'required' => false,
                'empty_data' => 0,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Meal::class]);
    }
}
