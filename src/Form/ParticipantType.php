<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username')
            ->add('prenom')
            ->add('nom')
            ->add('telephone')
            ->add('mail')
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options'  => ['label' => 'Password', 'hash_property_path' => 'password'],
                'second_options' => ['label' => 'Repeat Password'],
                'mapped' => false,
            ]);
            // Pour que le select soit relié à une entité
//            ->add('site', EntityType::class, [
//
//                // La classe à relier
//                'class' => Site::class,
//
//                /* Les valeurs des options du select, ici j'ai récupéré tous les sites à partir du SiteRepository dans le controller
//                Puis les ai passé en paramètre dans le même controller
//                */
//                'choices' => $options['sites'],
//                'choice_label' => 'nom',
//                'placeholder' => 'Choisissez un site',
//
//                /* Pas de selection multiple */
//                'multiple' => false,
//            ]);
//            ->add('photo')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
