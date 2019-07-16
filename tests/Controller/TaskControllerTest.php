<?php

namespace tests\Controller;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @internal
 * @coversNothing
 */
class TaskControllerTest extends WebTestCase
{
    /**
     * @var KernelBrowser
     */
    private $client;

    protected function setUp()
    {
        $this->client = self::createClient();
    }

    public function testClickButtonListTasksToDo()
    {
        $this->logIn('user', 'password');
        $crawler = $this->client->request('GET', '/');

        self::assertContains('/tasks', $crawler->filter('a')->extract(['href']));

        $link = $crawler->selectLink('Consulter la liste des tâches à faire')->link();
        $crawler = $this->client->click($link);

        self::assertContains('/logout', $crawler->filter('a')->extract(['href']));
        self::assertContains('/tasks/create', $crawler->filter('a')->extract(['href']));
        self::assertContains('Liste des tâches a faire', $crawler->filter('h1')->html());
        self::assertContains('/img/todolist_content.jpg', $crawler->filter('img')->extract(['src']));
    }

    public function testClickButtonListTasksDone()
    {
        $this->logIn('user', 'password');
        $crawler = $this->client->request('GET', '/');

        self::assertContains('/tasks', $crawler->filter('a')->extract(['href']));

        $link = $crawler->selectLink('Consulter la liste des tâches terminées')->link();
        $crawler = $this->client->click($link);

        self::assertContains('/logout', $crawler->filter('a')->extract(['href']));
        self::assertContains('/tasks/create', $crawler->filter('a')->extract(['href']));
        self::assertContains('Liste des tâches complété', $crawler->filter('h1')->html());
        self::assertContains('/img/todolist_content.jpg', $crawler->filter('img')->extract(['src']));
    }

    public function testCreateTask()
    {
        $this->logIn('user', 'password');
        $crawler = $this->client->request('GET', '/');

        $this->assertContains('/tasks/create', $crawler->filter('a')->extract(['href']));

        $link = $crawler->selectLink('Créer une nouvelle tâche')->link();
        $crawler = $this->client->click($link);

        $this->assertSame(2, $crawler->filter('input')->count());
        $this->assertSame(1, $crawler->filter('html:contains("Retour à la liste des tâches")')->count());

        $form = $crawler->selectButton('Ajouter')->form();
        $form['task[title]'] = 'TaskCreated';
        $form['task[content]'] = 'content';
        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        // verification de l'ajout
        self::assertSame(1, $crawler->filter('html:contains("TaskCreated")')->count());
        // présence du message alert
        self::assertSame(1, $crawler->filter('html:contains("La tâche a été bien été ajoutée")')->count());
    }

    public function testDeleteTaskAnonymousByAdmin()
    {
        $this->logIn('admin', 'password');

        $task = $this->getContainer()->get('doctrine')->getRepository(Task::class)->findOneByTitle('taskForDeleteByAdmin');

        $this->client->request('GET', '/tasks/'.$task->getId().'/delete');
        $crawler = $this->client->followRedirect();

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSame(0, $crawler->filter('html:contains("taskForDeleteByAdmin")')->count());
        self::assertSame(1, $crawler->filter('html:contains("La tâche a bien été supprimée.")')->count());
    }

    public function testErrorDeleteTaskUserByAdmin()
    {
        $this->logIn('admin', 'password');

        $task = $this->getContainer()->get('doctrine')->getRepository(Task::class)->findOneByTitle('taskForDelete');

        $this->client->request('GET', '/tasks/'.$task->getId().'/delete');

        self::assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteTask()
    {
        $this->logIn('user', 'password');

        $task = $this->getContainer()->get('doctrine')->getRepository(Task::class)->findOneByTitle('taskForDelete');

        $crawler = $this->client->request('GET', '/tasks');

        self::assertContains('/tasks/'.$task->getId().'/delete', $crawler->filter('a')->extract(['href']));

        $this->client->request('GET', '/tasks/'.$task->getId().'/delete');
        $crawler = $this->client->followRedirect();

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSame(0, $crawler->filter('html:contains("taskForDelete")')->count());
        self::assertSame(1, $crawler->filter('html:contains("La tâche a bien été supprimée.")')->count());
    }

    public function testEditTask()
    {
        $this->logIn('user', 'password');

        $task = $this->getContainer()->get('doctrine')->getRepository(Task::class)->findOneByTitle('taskForEdit');

        $crawler = $this->client->request('GET', '/tasks');

        // présence du boutton de modification
        self::assertContains('/tasks/'.$task->getId().'/edit', $crawler->filter('a')->extract(['href']));

        $crawler = $this->client->request('GET', '/tasks/'.$task->getId().'/edit');

        $form = $crawler->selectButton('Modifier la tâche')->form();
        $form['task[title]'] = 'editedTask';
        $form['task[content]'] = 'editedContent';
        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        // verification de l'édition
        self::assertSame(1, $crawler->filter('html:contains("editedTask")')->count());
        //présence du message alert
        self::assertSame(1, $crawler->filter('html:contains("La tâche a bien été modifiée.")')->count());
    }

    public function testToggleTask()
    {
        $this->logIn('user', 'password');

        $task = $this->getContainer()->get('doctrine')->getRepository(Task::class)->findOneByTitle('taskForToggle');

        $crawler = $this->client->request('GET', '/tasks');
        // présence du boutton de modification d'état
        self::assertContains('/tasks/'.$task->getId().'/toggle', $crawler->filter('a')->extract(['href']));

        //test de la non présence dans la liste to do
        $crawler = $this->client->request('GET', '/tasks/'.$task->getId().'/toggle');
        self::assertNotContains('/tasks/'.$task->getId().'/toggle', $crawler->filter('a')->extract(['href']));

        //test présence de la tache dans la liste is done
        $crawler = $this->client->request('GET', '/tasks/1');
        self::assertContains('/tasks/'.$task->getId().'/toggle', $crawler->filter('a')->extract(['href']));
    }

    public function logIn($userType, $password)
    {
        $session = $this->client->getContainer()->get('session');

        $user = $this->getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['username' => $userType]);

        $token = new UsernamePasswordToken($user, $password, 'main', ['ROLE_'.strtoupper($userType)]);

        $session->set('_security_'.'main', serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    private function getContainer()
    {
        self::bootKernel();
        // gets the special container that allows fetching private services
        return self::$container;
    }
}
