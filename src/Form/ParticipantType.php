<?php

namespace App\Form;

use App\Entity\Participant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormInterface;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
			->add('pseudo')
			->add('prenom')
			->add('nom')
			->add('telephone')
            ->add('mail', EmailType::class, [ 'label' => 'Email'])
			->add('motPasse', RepeatedType::class, [
				'invalid_message' => 'Les mots de passe ne correspondent pas.',
				'first_options'  => ['label' => 'Mot de passe'],
    			'second_options' => ['label' => 'Confirmation'],
				'getter' => function (Participant $participant, FormInterface $form): string {
                    return $participant->getPassword();
                },
                'setter' => function (Participant $participant, ?string $password, FormInterface $form): void {
                    $participant->setPassword($password);
                },
			])
			->add('campus')
			//->add('motPasse')
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
