<?php

namespace App\Form;

use App\Entity\ExerciseLog;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class ExerciseLogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('exerciseName', TextType::class, [
                'label' => 'Exercise Name',
                'attr'  => ['placeholder' => 'e.g. Morning Run', 'class' => 'form-control'],
                'constraints' => [new NotBlank()],
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Duration (min)',
                'attr'  => ['placeholder' => '30', 'min' => 1, 'class' => 'form-control'],
                'constraints' => [new NotBlank(), new Positive()],
            ])
            ->add('caloriesBurned', IntegerType::class, [
                'label' => 'Calories Burned',
                'attr'  => ['placeholder' => 'kcal', 'min' => 0, 'class' => 'form-control'],
                'constraints' => [new NotBlank(), new PositiveOrZero()],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ExerciseLog::class]);
    }
}
