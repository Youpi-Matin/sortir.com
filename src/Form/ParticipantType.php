<?php

namespace App\Form;

use App\Entity\Participant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
			->add('pseudo')
			->add('prenom', TextType::class, [ 'label' => 'Prénom', 'attr' => ['placeholder' => 'Name *']])
			->add('nom')
			->add('telephone')
            ->add('mail', EmailType::class, [ 'label' => 'Email'])
            ->add('motPasse')
			//->add('is_verified')
			->add('campus')
            //->add('administrateur')
            //->add('actif')
            //->add('inscriptions')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
