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
use Vich\UploaderBundle\Form\Type\VichImageType;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo')
            ->add('prenom')
            ->add('nom')
            ->add('telephone')
            ->add('mail', EmailType::class, ['label' => 'Email'])
            ->add('motPasse', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'first_options'  => [
                    'label' => 'Mot de passe',
                    'always_empty' => 'false',
                ],
                'second_options' => [
                    'label' => 'Confirmation',
                ],
                'getter' => function (Participant $participant, FormInterface $form): string {
                    return $participant->getPassword();
                },
                'setter' => function (Participant $participant, ?string $password, FormInterface $form): void {
                    $participant->setPassword((string)$password);
                },
                'required' => false,
            ])
            ->add('campus')
            ->add('imageFile', VichImageType::class, [
                'label' => 'Ma photo',
                'required' => false,
                'delete_label' => 'Supprimer',
                'download_label' => 'Télécharger',
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
            'sanitize_html' => true,
        ]);
    }
}
