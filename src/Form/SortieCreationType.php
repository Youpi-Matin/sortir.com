<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieCreationType extends AbstractType
{
    /**
     * Configure la sanitization des input HTML
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
            'sanitize_html' => true,
        ]);
    }

    /**
     * Formulaire de creation de sortie.
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie:',
                'required' => true,
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Date et heure de la sortie:',
                'required' => true,
                'widget' => 'single_text',
                'empty_data' => new \DateTime('now'),
            ])
            ->add('dateLimiteInscription', DateType::class, [
                'label' => 'Date limite d\'inscription',
                'required' => true,
                'widget' => 'single_text',

            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => 'Nombre de places:',
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Duree (minutes):',
                'required' => true,
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description et Infos:',
                'required' => false,
            ])
            ->add('ville', EntityType::class, [
                'class' => Ville::class,
                'choice_label' => 'nom',
                'placeholder' => '...',
                'mapped' => false,
            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'placeholder' => '...',
                'empty_data' => '',
            ])
            ->add('ajouter_lieu', ButtonType::class, [
                'label' => 'Ajouter un lieu',
                'attr' => [
                    'class' => 'button js-modal',
                    'data-controller' => 'lieu-ajouter',
                    'data-modal-content-id' => 'modalLieu',
                    'data-modal-prefix-class' => 'simple simple-animated',
                ]
            ])
        ;
    }
}
