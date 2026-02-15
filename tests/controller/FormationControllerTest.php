<?php

namespace App\Tests\Controller;  // CORRECTION: Tests avec T majuscule

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FormationControllerTest extends WebTestCase  // CORRECTION: sans 's' à Formation
{
    public function testAccesPage()
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('GET', '/formations');
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());  // CORRECTION: getStatusCode()
    }

    public function testContenuPage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/formations');
        $this->assertSelectorTextContains('th', 'formation');
        $this->assertCount(5, $crawler->filter('th'));
    }

    public function testLinkFormation()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/formations');
        
        // Méthode plus robuste: chercher le premier lien vers une formation
        $formationLink = $crawler->filter('a[href*="/formations/formation/"]')->first();
        
        // Vérifier qu'on a trouvé un lien
        $this->assertCount(1, $formationLink, "Aucun lien vers une formation trouvé");
        
        // Récupérer l'URL du lien
        $link = $formationLink->attr('href');
        
        // Cliquer sur le lien
        $crawler = $client->request('GET', $link);
        
        // Vérifier la réponse
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        
        // Récupérer l'URI
        $uri = $client->getRequest()->server->get("REQUEST_URI");
        
        // Vérifier que l'URI contient le pattern d'une formation
        $this->assertMatchesRegularExpression('#/formations/formation/\d+#', $uri, "L'URI ne correspond pas à une formation");
    }

    public function testFiltreFormation()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/formations');
        
        // simulation de la soumission du formulaire
        $form = $crawler->selectButton('filtrer')->form();
        $crawler = $client->submit($form, [
            'recherche' => 'Eclipse n°3 : GitHub et Eclipse'
        ]);
        
        // vérifie le nombre de lignes obtenues
        $this->assertCount(1, $crawler->filter('h5'));
        
        // vérifie si la formation correspond à la recherche
        $this->assertSelectorTextContains('h5', 'Eclipse n°3 : GitHub et Eclipse');
    }
    
    public function testSortFormation()
    {
        $client = static::createClient();
        
        // Tri par titre DESC
        $crawler = $client->request('GET', '/formations/tri/title/DESC');
        $this->assertSelectorTextContains('h5', 'UML : Diagramme de paquetages');
        
        // Tri par playlist DESC
        $crawler = $client->request('GET', '/formations/tri/name/DESC/playlist');
        $this->assertSelectorTextContains('h5', 'C# : ListBox en couleur');
        
        // Tri par date ASC
        $crawler = $client->request('GET', '/formations/tri/publishedAt/ASC');
        $this->assertSelectorTextContains('h5', 'Cours UML (1 à 7 / 33) : introduction et cas d\'utilisation');
    }
}