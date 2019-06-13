<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class LoginType extends AbstractType
{
    /**
     * @var string/null
     */
    protected $lastUsername;

    /**
     * LoginType constructor.
     * @param null $lasUsername
     */
    public function __construct($lastUsername = null)
    {
        $this->lastUsername = $lastUsername;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('_username', TextType::class, [
                'label' => 'form.login.username.label',
                'attr' => [
                    'value' => $this->lastUsername,
                    'autofocus' => true,
                ]
            ])
            ->add('_password', PasswordType::class, [
                'label' => 'form.login.password.label',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return null;
    }
}
