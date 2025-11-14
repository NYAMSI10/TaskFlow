<?php

namespace App\Form;

use App\Entity\Shared;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SharedType extends AbstractType
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->security->getUser();

        $builder
            ->add('comment', TextareaType::class, [
                'label' => 'Commentaire',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Ajouter un commentaire...',
                    'class' => 'w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500'
                ]
            ])
            ->add('permission', ChoiceType::class, [
                'label' => 'Permission',
                'choices' => [
                    'Créateur' => 'Créateur',
                    'Contributeur' => 'Contributeur',

                ],
                'attr' => ['class' => 'w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500']
            ])
            ->add('user', EntityType::class, [
                'label' => 'Utilisateurs',
                'class' => User::class,
                'choice_label' => 'email',
                'multiple' => true,
                'query_builder' => function (EntityRepository $er) use ($user) {
                    return $er->createQueryBuilder('u')
                        ->where('u.id != :user')
                        ->setParameter('user', $user)
                        ->orderBy('u.email', 'ASC');
                },
                'attr' => [
                    'class' => 'select2-multiple w-full border rounded-lg px-4 py-2',
                    'data-placeholder' => 'Sélectionner des utilisateurs...',
                ]
            ])
            ->add('task', EntityType::class, [
                'label' => 'Tâche',
                'class' => Task::class,
                'choice_label' => 'title',
                'required' => false,
                'query_builder' => function (EntityRepository $er) use ($user) {
                    return $er->createQueryBuilder('t')
                        ->where('t.user = :user')
                        ->setParameter('user', $user)
                        ->orderBy('t.title', 'ASC');
                },
                'attr' => [
                    'class' => 'w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Shared::class,
        ]);
    }
}
