<?php

namespace App\Form;

use App\Entity\Site;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie',
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Début de la sortie',
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée',
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('dateLimiteInscription ', DateTimeType::class, [
                'label' => 'Date limite d\'inscription',
                'attr' => [
                    'class' => 'form-control',
                ]

            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => 'Début de la sortie',
                'attr' => [
                    'class' => 'form-control',
                ]

            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description de la sortie',
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('site', EntityType::class, [
                'label' => 'Site de rattachement',
                'class' => Site::class,
                'choice_label' => 'id',
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
