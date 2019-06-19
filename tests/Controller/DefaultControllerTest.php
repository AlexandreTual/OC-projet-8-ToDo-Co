<?php

namespace tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class DefaultControllerTest extends WebTestCase
{
    public $client;

    protected function setUp(): void
    {
        $this->client =self::createClient();
    }

    public function testHomepageLogout()
    {
        $this->client->request('GET', '/');
        self::assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testHomepageLogin()
    {
        $this->logInUser();
        $crawler = $this->client->request('GET', '/');

        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        self::assertContains('/tasks/create', $crawler->filter('a')->extract(['href']));
        self::assertContains('/tasks', $crawler->filter('a')->extract(['href']));
        self::assertContains('/tasks/1', $crawler->filter('a')->extract(['href']));

    }

    public function logInUser()
    {
        $session = $this->client->getContainer()->get('session');

        $token = new UsernamePasswordToken('user', null, 'main', ['ROLE_USER']);

        $session->set('_security_'.'main', serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
}
