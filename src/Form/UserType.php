<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'form.user.username.label', ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'form.user.invalid_message',
                'required' => true,
                'first_options' => ['label' => 'form.user.first_options.label'],
                'second_options' => ['label' => 'form.user.second_options.label'],
            ])
            ->add('email', EmailType::class, ['label' => 'form.user.email.label'])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'role.user' => 'ROLE_USER',
                    'role.admin' => 'ROLE_ADMIN',
                ],
                'label' => 'form.user.roles.label',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
