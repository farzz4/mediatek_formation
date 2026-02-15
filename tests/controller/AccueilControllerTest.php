<?php

namespace App\tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccueilControllerTest extends WebTestCase
{
    public function testAccesPage()
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('GET', '/');
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode()); // Correction: getStatuscode() -> getStatusCode()
    }

    public function testContenuPage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertSelectorTextContains('h3', 'MediaTek86');
        $this->assertSelectorTextContains('h5', 'Eclipse n°8');
        $this->assertCount(2, $crawler->filter('h5'));
    }

    public function testLinkFormation()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        
        // Méthode 1: Chercher un lien qui contient "formation" dans son href
        $formationLink = $crawler->filter('a[href*="formation"]')->first();
        
        // Vérifier qu'on a bien trouvé un lien
        $this->assertCount(1, $formationLink, "Aucun lien vers une formation trouvé");
        
        // Récupérer l'URL du lien
        $linkUrl = $formationLink->attr('href');
        
        // Cliquer sur le lien
        $crawler = $client->request('GET', $linkUrl);
        
        // Vérifier que la page s'est bien chargée
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        
        // Récupérer l'URI actuelle
        $uri = $client->getRequest()->server->get("REQUEST_URI");
        
        // Vérifier que l'URI contient bien "formation" (sans vérifier l'ID exact)
        $this->assertStringContainsString('formation', $uri, "L'URI ne contient pas 'formation'");
        
        // Alternative: Si vous voulez vérifier le pattern exact
        // $this->assertMatchesRegularExpression('#/formations/formation/\d+#', $uri);
    }
}