<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Repository\SiteRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterSortieListType extends AbstractType

{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $today = new \DateTime('now', new \DateTimeZone('Europe/Paris'));

        $builder
            // Pour que le select soit relié à une entité
            ->add('site', EntityType::class, [

                // La classe à relier
                'class' => Site::class,

                /* Les valeurs des options du select, ici j'ai récupéré tous les sites à partir du SiteRepository dans le controller
                Puis les ai passé en paramètre dans le même controller
                */
                'query_builder' => function (SiteRepository $sr): QueryBuilder {
                    return $sr->createQueryBuilder('s')
                        ->orderBy('s.nom', 'ASC');
                },
                'choices' => $options['sites'],
                'choice_label' => 'nom',
                'placeholder' => 'Choisissez un site',

                /* Pas de selection multiple */
                'multiple' => false,
                'required' => false,
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('search', SearchType::class, [
                'label' => 'Rechercher',
                'attr' => [
                    'placeholder' => 'Je cherche un lieu, une sortie, une ville...',
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'required' => false,
            ])
            ->add('dateMin', DateTimeType::class, [
                'label' => ' Entre le ',
                'attr' => [
                    'class' => 'form-control',
                ],
                'required' => false,
                'data' => $today,
                'label_attr' => [
                    'class' => 'form-label',
                ],
            ])
            ->add('dateMax', DateTimeType::class, [
                'label' => ' Et le',
                'attr' => [
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'required' => false,
                'data' => $today,
            ])
            ->add('checkboxes', ChoiceType::class, [
                'label' => 'Je filtre par : ',
                'required' => false,
                'expanded' => true,
                'multiple' => true,
                'choices' => [
                    'Sorties dont je suis l\'organisateur·trice' => 'mySorties',
                    'Sorties auxquelles je suis inscrit·e' => 'sortiesRegisteredAt',
                    'Sorties auxquelles je ne suis pas inscrit·e' => 'sortiesUnregisteredAt',
                    'Sorties passées' => 'pastSorties'
                ],
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'attr' => [
                    'class' => 'form-control',
                    'style' => 'display:flex;',
                ],


            ])
//            ->add('submit', SubmitType::class, [
//                'label' => 'Rechercher',
//            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'sites' => [],
            /* Le token csrf n'est pas obligatoire pour cette requête GET puisqu'elle n'apporte aucune modification
            Cela permet aussi de ne pas afficher le champ csrf dans l'URL
             */
            'csrf_protection' => false,

            // Permet d'ajouter d'autres champs au formulaire: cf submit en HTML dans le twig
            "allow_extra_fields" => true
        ]);
    }

    /* Pour transformer le paramètre d'URL site_select[site]=13 (nom du form + nom du champ + valeur)
        en site=13 (nom du champ + valeur)
        https://symfony.com/doc/current/forms.html#changing-the-form-field-names-and-ids
    */
    public function getBlockPrefix(): string
    {
        return '';
    }

}
