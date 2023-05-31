<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConferenceControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = self::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('a');
        $response_html = $client->getResponse()->getContent();
        $this->assertStringContainsString('Minsk', $response_html);
        $this->assertStringContainsString('Kiev', $response_html);
    }
}