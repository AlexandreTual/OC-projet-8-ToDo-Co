<?php

namespace tests\Form;

use App\Entity\User;
use App\Form\EditPasswordType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class EditPasswordTypeTest extends TypeTestCase
{
    public function testForm()
    {
        $formData = [
            'password' => [
                'first' => 'password',
                'second' => 'password',
            ],
        ];

        $object = new User();
        $objectToCompare = new User();

        $object
            ->setPassword('password')
        ;

        $form = $this->factory->create(EditPasswordType::class, $objectToCompare);
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

    protected function getExtensions()
    {
        $validator = Validation::createValidator();

        return [
            new ValidatorExtension($validator),
        ];
    }
}
