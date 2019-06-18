<?php

namespace tests\Form;

use App\Entity\Task;
use App\Form\TaskType;
use Symfony\Component\Form\Test\TypeTestCase;

class TaskTypeTest extends TypeTestCase
{
    public function testForm()
    {
        $formData = [
            'title' => 'un super titre',
            'content' => 'un contenu riche',
        ];
        $datetime = new \DateTime();

        $object = new Task();
        $objectToCompare = new Task();
        $objectToCompare->setCreatedAt($datetime);

        $object
            ->setTitle('un super titre')
            ->setContent('un contenu riche')
            ->setCreatedAt($datetime);

        $form = $this->factory->create(TaskType::class, $objectToCompare);
        $form->submit($formData);

        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isValid());

        $this->assertEquals($object, $objectToCompare);
        $this->assertInstanceOf(Task::class, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}