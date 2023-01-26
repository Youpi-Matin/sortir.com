<?php

namespace App\Form;

use App\Entity\Campus;
use App\Model\SortieFiltre;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieFiltreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom'
            ])
            ->add('search', SearchType::class, [
                'label' => '...dont le titre contient...',
                'required' => false,
            ])
            ->add('dateMin', DateType::class, [
                'widget' => 'single_text',
                // this is actually the default format for single_text
                // 'format' => 'yyyy-MM-dd',
                'required' => false
            ])
            ->add('dateMax', DateType::class, [
                'widget' => 'single_text',
                // this is actually the default format for single_text
                // 'format' => 'yyyy-MM-dd',
                'required' => false
            ])
            ->add('organisateurice', CheckboxType::class, [
                'label' => 'j\'en suis l \'organisateur·ice',
                'required' => false
            ])
            ->add('inscrite', CheckboxType::class, [
                'label' => 'j\'y suis inscrit·e',
                'required' => false
            ])
            ->add('noninscrite', CheckboxType::class, [
                'label' => 'je n\'y suis pas inscrit·e',
                'required' => false
            ])
            ->add('passee', CheckboxType::class, [
                'label' => 'passée ?',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SortieFiltre::class,
        ]);
    }
}
