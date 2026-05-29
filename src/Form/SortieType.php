<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use DateTime;
use DateTimeZone;
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
                'label' => 'Nom de la sortie',
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Début de la sortie',
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
                'model_timezone' => 'Europe/Paris',
                'view_timezone' => 'Europe/Paris'
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée',
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'attr' => [
                    'class' => 'form-control',
                    'min' => 30,
                    'max' => 1000,
                ]
            ])
            ->add('dateLimiteInscription', DateTimeType::class, [
                'label' => 'Date limite d\'inscription',
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
                'model_timezone' => 'Europe/Paris',
                'view_timezone' => 'Europe/Paris'

            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => 'Nombre d\'inscriptions max',
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'attr' => [
                    'class' => 'form-control',
                    'min' => 3,
                    'max' => 50,
                ]

            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description de la sortie',
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('lieu', EntityType::class, [
                'label' => 'Lieu',
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'attr' => [
                    'class' => 'form-control',
                ]
            ])


            //            ->add('site', EntityType::class, [
//                'class' => Site::class,
//                'choice_label' => 'nom',
//                'multiple' => false,
//                'attr' => [
//                    'class' => 'form-control'
//                ]
//            ])
//            ->add('organisateur', EntityType::class, [
//                'class' => Participant::class,
//                'choice_label' => 'username',
//                'multiple' => false,
//                'attr' => [
//                    'class' => 'form-control'
//                ]
//            ])
            //TODO : select pour lieux + ajout lieu
//            ->add('lieu', EntityType::class, [
//                'class' => Lieu::class,
//                'choice_label' => 'nom',
//                'multiple' => false,
//                'attr' => [
//                    'class' => 'form-control'
//                ]
//            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
