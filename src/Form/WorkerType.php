<?php

namespace App\Form;

use App\Domain\WorkCycle\Entity\Worker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Imię i Nazwisko',
                'attr' => ['class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm'],
            ])
            ->add('shortName', TextType::class, [
                'label' => 'Skrót (używany na grafiku)',
                'attr' => ['class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm'],
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'Aktywny',
                'required' => false,
                'attr' => ['class' => 'h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded'],
                'label_attr' => ['class' => 'ml-2 block text-sm text-gray-900']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Worker::class,
        ]);
    }
}
