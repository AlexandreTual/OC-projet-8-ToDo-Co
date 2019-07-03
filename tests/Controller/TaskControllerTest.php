<?php

namespace tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
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

    public function testClickButtonCreate()
    {
        $this->logIn('user', 'password');
        $crawler = $this->client->request('GET', '/');

        $this->assertContains('/tasks/create', $crawler->filter('a')->extract(['href']));

        $link = $crawler->selectLink('Créer une nouvelle tâche')->link();
        $crawler = $this->client->click($link);

        $this->assertSame(2, $crawler->filter('input')->count());
        $this->assertSame(1, $crawler->filter('html:contains("Retour à la liste des tâches")')->count());
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
