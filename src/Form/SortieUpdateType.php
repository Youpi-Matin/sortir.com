<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieUpdateType extends AbstractType
{
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
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Duree (minutes):',
                'required' => true,
            ])
            ->add('dateLimiteInscription', DateType::class, [
                'label' => 'Date limite d\'inscription',
                'required' => true,
                'widget' => 'single_text',

            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => 'Nombre de places:',
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description et Infos:',
                'required' => false,
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
            'sanitize_html' => true,
        ]);
    }
}
