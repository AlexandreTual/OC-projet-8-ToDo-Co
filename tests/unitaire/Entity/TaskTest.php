<?php

namespace tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $task = new Task();

        $titre = 'un titre';
        $content = 'un super texte !!';
        $createdAt = new \DateTime();
        $user = new User();

        $task
            ->setTitle($titre)
            ->setContent($content)
            ->setCreatedAt($createdAt)
            ->setUser($user);

        $this->assertEquals(null, $task->getId());
        $this->assertEquals($titre, $task->getTitle());
        $this->assertEquals($content, $task->getContent());
        $this->assertEquals($createdAt, $task->getCreatedAt());
        $this->assertEquals($user, $task->getUser());
        $this->assertEquals(false, $task->isDone());
    }

    public function testStateChange()
    {
        $task = new Task();

        $task->toggle(true);

        $this->assertEquals(true, $task->isDone());
    }
}
