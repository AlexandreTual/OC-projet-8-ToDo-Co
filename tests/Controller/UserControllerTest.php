<?php

namespace tests\Controller;

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
class UserControllerTest extends WebTestCase
{
    /**
     * @var KernelBrowser
     */
    private $client;

    protected function setUp()
    {
        $this->client = self::createClient();
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

    public function testAdminDisplayListUser()
    {
        $this->logIn('admin', 'password');
        $crawler = $this->client->request('GET', '/users');

        $nBUser = count($this->getContainer()->get('doctrine')->getRepository(User::class)->findAll());

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertSame(1, $crawler->filter('html:contains("Liste des utilisateurs")')->count());
        self::assertContains('/users/create', $crawler->filter('a')->extract(['href']));
        self::assertContains('/logout', $crawler->filter('a')->extract(['href']));

        // test présence tableau
        self::assertSame(1, $crawler->filter('html:contains("Nom d\'utilisateur")')->count());
        self::assertSame(1, $crawler->filter('html:contains("Email")')->count());
        self::assertSame(1, $crawler->filter('html:contains("Rôles")')->count());
        self::assertSame(1, $crawler->filter('html:contains("Actions")')->count());

        // test le nombre de ligne représentant chaque utilisateur (le + 1 correspond a l'entête du tableau)
        self::assertSame($nBUser + 1, $crawler->filter('tr')->count());
    }

    public function testAccessDeniedForUserPageListUser()
    {
        $this->logIn('user', 'password');
        $this->client->request('GET', '/users');

        self::assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testEditUser()
    {
        $user = $this->getContainer()->get('doctrine')->getRepository(User::class)->findOneByUsername('user');
        // Without user Auth
        $this->client->request('GET', '/users/'.$user->getId().'/edit');
        self::assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

        // With user auth
        $this->logIn('user', 'password');
        $this->client->request('GET', '/users/'.$user->getId().'/edit');
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // with admin
        $this->logIn('admin', 'password');
        $this->client->request('GET', '/users/'.$user->getId().'/edit');
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    private function getContainer()
    {
        self::bootKernel();
        // gets the special container that allows fetching private services
        return self::$container;
    }

    /*public function testCreationUser()
    {
        $this->logIn('user', 'password');
        $crawler = $this->client->request('GET', '/');

        self::assertSame(1, $crawler->filter('html:contains("Créer un utilisateur")')->count());

        $link = $crawler->selectLink('Créer un utilisateur')->link();
        $crawler = $this->client->click($link);

        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[username]'] = 'userTest';
        $form['user[password][first]'] = 'password';
        $form['user[password][second]'] = 'password';
        $form['user[email]'] = 'userTest@gmail.com';
        $form['user[roles]'] = 'ROLE_USER';
        $this->client->submit($form);
        $this->client->followRedirect();

        echo $this->client->getResponse()->getContent();

        self::assertSame(1,$crawler->filter('html:contains("L\'utilisateur a bien été ajouté")')->html());
    }*/
}
