<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @codeCoverageIgnore
 */
class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        // creation user [ROLE_USER]
        $user = $this->createUser('user');
        $manager->persist($user);

        // creation user [ROLE_ANONYMOUS]
        $anonymous = $this->createUser('anonymous');
        $manager->persist($anonymous);

        // creation user [ROLE_ADMIN]
        $admin = $this->createUser('admin');
        $manager->persist($admin);

        // creation task
        for ($i = 0; $i < 10; ++$i) {
            $manager->persist($this->createTask($user, $i));
        }

        for ($j = 0; $j < 100; ++$j) {
            $manager->persist($this->createTask($anonymous, $j));
        }

        for ($k = 0; $k < 10; ++$k) {
            $manager->persist($this->createTask($admin, $k));
        }

        //creation task pour tester la suppression
        $manager->persist($this->createTask($user, 0, 'taskForDelete'));
        // creation task pour tester l'édition
        $manager->persist($this->createTask($user, 0, 'taskForEdit'));
        // creation task pour tester toggle
        $manager->persist($this->createTask($user, 0, 'taskForToggle'));
        // creation task by anonymous pour suppression par admin
        $manager->persist($this->createTask($anonymous, 0, 'taskForDeleteByAdmin'));

        $manager->flush();
    }

    /**
     * @param string $type
     * @param string $password
     *
     * @return User
     */
    public function createUser(string $type, string $password = 'password'): User
    {
        $user = new User();

        return $user
            ->setUsername($type)
            ->setPassword($this->encoder->encodePassword($user, $password))
            ->setEmail($type.'@todo-co.com')
            ->setRoles('ROLE_'.strtoupper($type))
        ;
    }

    /**
     * @param User   $user
     * @param int    $isDone
     * @param string $title
     * @param string $content
     *
     * @return Task
     */
    public function createTask(User $user, int $isDone, string $title = 'une tâche', string $content = 'un contenu'): Task
    {
        $task = new Task();

        $task
            ->setTitle($title)
            ->setContent($content)
            ->setUser($user)
            ->toggle(1 == $isDone % 2 ? '1' : '0')
        ;

        return $task;
    }
}
