<?php

namespace tests\Form;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class UserTypeTest extends TypeTestCase
{
    protected function getExtensions()
    {
        $validator = Validation::createValidator();

        return [
            new ValidatorExtension($validator),
        ];
    }

    public function testForm()
    {
        $formData = [
            'username' => 'Alexandre',
            'password' => [
              'first' => 'password',
              'second' => 'password',
            ],
            'email' => 'tual.alexandre@gmail.com',
            'roles' => 'ROLE_USER',
        ];

        $object = new User();
        $objectToCompare = new User();

        $object
            ->setUsername('Alexandre')
            ->setPassword('password')
            ->setEmail('tual.alexandre@gmail.com')
            ->setRoles('ROLE_USER');


        $form = $this->factory->create(UserType::class, $objectToCompare);
        $form->submit($formData);

        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isValid());

        $this->assertEquals($object, $objectToCompare);
        $this->assertInstanceOf(User::class, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
