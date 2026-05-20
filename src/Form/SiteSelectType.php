<?php

namespace App\Form;

use App\Entity\Site;
use App\Entity\Sortie;
use App\Repository\SiteRepository;
use phpDocumentor\Reflection\Types\String_;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteSelectType extends AbstractType
{



    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            // Pour que le select soit relié à une entité
            ->add('site', EntityType::class, [

                // La classe à relier
                'class' => Site::class,

                /* Les valeurs des options du select, ici j'ai récupéré tous les sites à partir du SiteRepository dans le controller
                Puis les ai passé en paramètre dans le même controller
                */
                'choices' => $options['sites'],
                'choice_label' => 'nom',
                'placeholder' => 'Choisissez un site',

                /* Pas de selection multiple */
                'multiple' => false,
            ])
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
