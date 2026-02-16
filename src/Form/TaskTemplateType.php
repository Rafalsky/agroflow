<?php

namespace App\Form;

use App\Domain\WorkCycle\Entity\TaskTemplate;
use App\Domain\WorkCycle\Entity\Worker;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskTemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nazwa zadania',
            ])
            ->add('points', IntegerType::class, [
                'label' => 'Punkty',
            ])
            ->add('priority', ChoiceType::class, [
                'label' => 'Priorytet',
                'choices' => [
                    'Normalny' => 'NORMAL',
                    'Pilny' => 'URGENT',
                ],
            ])
            ->add('weekday', ChoiceType::class, [
                'label' => 'Dzień tygodnia',
                'choices' => [
                    'Poniedziałek' => 1,
                    'Wtorek' => 2,
                    'Środa' => 3,
                    'Czwartek' => 4,
                    'Piątek' => 5,
                    'Sobota' => 6,
                    'Niedziela' => 7,
                ],
            ])
            ->add('recurring', CheckboxType::class, [
                'label' => 'Cykliczne (co tydzień)',
                'required' => false,
            ])
            ->add('category', TextType::class, [
                'label' => 'Kategoria (opcjonalnie)',
                'required' => false,
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'Aktywne',
                'required' => false,
            ])
            ->add('defaultWorker', EntityType::class, [
                'class' => Worker::class,
                'choice_label' => 'name',
                'label' => 'Domyślny pracownik',
                'required' => false,
                'placeholder' => '-- Brak --',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TaskTemplate::class,
        ]);
    }
}
