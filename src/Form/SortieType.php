<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use DateTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Nom',
                    'class' => 'form-control'
                ]
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Début de la sortie',
                'required' => true,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée',
                'required' => true,
                'attr' => [
                    'class' => 'form-control'
                ]

            ])
            ->add('dateLimiteInscription', DateTimeType::class, [
                'label' => 'Date limite d\'inscription',
                'required' => true,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => 'Nombre de places disponibles',
                'required' => true,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'nom',
                'multiple' => false,
            ])
            ->add('organisateur', EntityType::class, [
                'class' => Participant::class,
                'choice_label' => 'username',
                'multiple' => false,
            ])
            ->add("Creer", SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
