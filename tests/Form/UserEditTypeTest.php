<?php

namespace tests\Form;

use App\Entity\User;
use App\Form\UserEditType;
use Symfony\Component\Form\Test\TypeTestCase;

class UserEditTypeTest extends TypeTestCase
{
    public function testForm()
    {
        $formData = [
            'username' => 'alex',
            'email' => 'tual.alexandre@gmail.com',
        ];

        $object = new User();
        $objectToCompare = new User();

        $object
            ->setUsername('alex')
            ->setEmail('tual.alexandre@gmail.com')
        ;

        $form = $this->factory->create(UserEditType::class, $objectToCompare);
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
