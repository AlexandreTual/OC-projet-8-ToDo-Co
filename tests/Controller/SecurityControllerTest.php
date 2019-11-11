<?php

namespace tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    /**
     * @var KernelBrowser
     */
    private $client;

    protected function setUp()
    {
        $this->client = self::createClient();
    }

    public function testLoginFunctionWithCorrectLogin()
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Connexion')->form();
        $form['_username'] = 'user';
        $form['_password'] = 'password';
        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        self::assertSame(1, $crawler->filter('html:contains("Bienvenue sur Todo List, l\'application vous permettant de gérer l\'ensemble de vos tâches sans effort !")')->count());
    }

    public function testDisplayMessageErrorLoginIncorrectUsername()
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Connexion')->form();
        $form['_username'] = 'badUser';
        $form['_password'] = 'password';
        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        self::assertSame(1, $crawler->filter('html:contains("L\'identifiant ou le mot de passe est incorrect")')->count());
    }

    public function testDisplayMessageErrorLoginIncorrectPassword()
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Connexion')->form();
        $form['_username'] = 'user';
        $form['_password'] = 'badPassword';
        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        self::assertSame(1, $crawler->filter('html:contains("L\'identifiant ou le mot de passe est incorrect")')->count());
    }

    public function testDisplayMessageErrorLoginIncorrectUsernameAndPassword()
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Connexion')->form();
        $form['_username'] = 'badUser';
        $form['_password'] = 'badPassword';
        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        self::assertSame(1, $crawler->filter('html:contains("L\'identifiant ou le mot de passe est incorrect")')->count());
    }
}
