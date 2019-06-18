<?php

namespace test\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    private $clientSf;

    protected function setUp()
    {
        $this->clientSf = static::createClient();
    }

    public function testPageLogin()
    {
        $crawler = $this->clientSf->request('GET', '/login');

        //website name
        $this->assertContains('To Do List app', $crawler->filter('a')->html());
        // website logo
        $this->assertContains('/img/Logo_OpenClassrooms.png', $crawler->filter('img')->extract(['src']));

        $this->assertContains('/img/todolist_homepage.jpg', $crawler->filter('img')->extract(['src']));

        // form login
        $this->assertContains('_username', $crawler->filter('input')->extract(['name']));
        $this->assertContains('_password', $crawler->filter('input')->extract(['name']));
        $this->assertContains('Connexion', $crawler->filter('button')->html());
    }

    public function submitFormLoginWithCorrectIdentifiers()
    {
        $crawler = $this->clientSf->request('GET', '/login');

        $form = $crawler->selectButton('submit')->form();

        $form['_username'] = 'alex';
        $form['_password'] = 'password';

        $this->clientSf->submit($form);

        $this->assertResponseRedirects('http://127.0.0.1:8000/', 200);
    }

    public function submitFormLoginOk()
    {
        $crawler = $this->clientSf->request('GET', '/login');

        $form = $crawler->selectButton('submit')->form();

        $form['_username'] = 'alex';
        $form['_password'] = 'password';

        $this->clientSf->submit($form);

        $this->assertResponseRedirects('http://127.0.0.1:8000/', 200);
    }

    public function submitFormLoginWithBadPassword()
    {
        $crawler = $this->clientSf->request('GET', '/login');

        $form = $crawler->selectButton('submit')->form();

        $form['_username'] = 'alex';
        $form['_password'] = 'mauvaispassword';

        $this->clientSf->submit($form);

        $this->assertContains('alert', $crawler->filter('div')->extract(['role']));
        $this->assertNotContains('/tasks/create', $crawler->filter('a')->extract(['href']));
    }

    public function submitFormLoginWithBadUsername()
    {
        $crawler = $this->clientSf->request('GET', '/login');

        $form = $crawler->selectButton('submit')->form();

        $form['_username'] = 'mauvaisNom';
        $form['_password'] = 'password';

        $this->clientSf->submit($form);

        $this->assertContains('alert', $crawler->filter('div')->extract(['role']));
        $this->assertNotContains('/tasks', $crawler->filter('a')->extract(['href']));
    }
}
