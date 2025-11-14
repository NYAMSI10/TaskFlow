<?php

namespace App\Form;

use App\Entity\Task;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'placeholder' => 'Titre de la tâche',
                    'class' => 'w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Description de la tâche...',
                    'class' => 'w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500'
                ]
            ])
            ->add('deadline', DateType::class, [
                'label' => 'Date limite',
                'widget' => 'single_text',
                'attr' => ['class' => 'w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500'],
            ])
            ->add('priority', ChoiceType::class, [
                'label' => 'Priorité',
                'choices' => [
                    'Basse' => 'Basse',
                    'Moyenne' => 'Moyenne',
                    'Haute' => 'Haute',
                ],
                'attr' => ['class' => 'w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500']
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'À faire' => 'À faire',
                    'En cours' => 'En cours',
                    'Terminée' => 'Terminée',
                ],
                'attr' => ['class' => 'w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500']
            ]);;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
