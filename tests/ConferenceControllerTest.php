<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConferenceControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }
    public function testIndex(): void
    {
        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('a');
        $response_html = $this->client->getResponse()->getContent();
        $this->assertStringContainsString('Minsk', $response_html);
        $this->assertStringContainsString('Kiev', $response_html);
    }

    public function testCommentSubmission(): void
    {
        $crawler = $this->client->request('GET', '/conference/minsk-2012');
        $this->assertStringContainsString('Add your feedback', $this->client->getResponse()->getContent());
        $buttonCrawlerNode = $crawler->selectButton('Submit');
        $form = $buttonCrawlerNode->form();
        $form[$form->getName() . '[author]'] = 'Bilbo';
        $form[$form->getName() . '[text]'] = 'WOW!!!';
        $form[$form->getName() . '[email]'] = 'baggins@shire.com';

        $this->client->submit($form);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorExists('h5.conference-comments-count');
        $this->assertSelectorExists('p.card-text');
        $this->assertSelectorTextContains('p.card-text', 'WOW!!!');
    }

    public function testConferencePage(): void
    {
        $crawler = $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $a_elements = $crawler->filter('a.conference-btn');
        $this->assertStringContainsString('Minsk', $a_elements->getNode(0)->nodeValue);

        $this->assertCount(2, $crawler->filter('a.conference-btn'));
        $link_text = trim($a_elements->getNode(0)->textContent);
        $this->client->clickLink($link_text);

        $this->assertResponseIsSuccessful();
        $this->assertPageTitleContains('Minsk');
        $this->assertSelectorTextContains('h1', 'Minsk');
        $this->assertSelectorExists('h5.conference-comments-count');
        $this->assertSelectorTextContains('h5.conference-comments-count', 'There are');
    }
}