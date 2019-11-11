<?php

namespace tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $user = new User();

        $username = 'Alexandre';
        $email = 'alexandre@gmail.com';
        $password = 'password';
        $role = ['ROLE_USER'];
        $task = new Task();

        $user
            ->setUsername($username)
            ->setEmail($email)
            ->setPassword($password)
            ->setRoles($role)
            ->addTask($task)
        ;

        $this->assertEquals(null, $user->getId());
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($task, $user->getTasks()[0]);

        //UserInterface
        $this->assertEquals($username, $user->getUsername());
        $this->assertEquals($password, $user->getPassword());
        $this->assertEquals(null, $user->getSalt());
        $this->assertEquals($role, $user->getRoles());

        $user->setRoles('USER_ADMIN');
        $this->assertEquals(['USER_ADMIN'], $user->getRoles());
    }

    public function testRemoveTask()
    {
        $user = new User();
        $task = new Task();

        $user
            ->addTask($task)
        ;

        $user->removeTask($task);
        $this->assertEquals(null, $user->getTasks()[0]);
    }
}
