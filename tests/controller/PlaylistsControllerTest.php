<?php

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PlaylistsControllerTest extends WebTestCase
{
    
    public function testAccesPage()
    {
        $client = static::createClient();
        $client->catchExceptions(false);
        $client->request('GET', '/playlists');
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testContenuPage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/playlists');
        $this->assertSelectorTextContains('th', 'playlist');
        $this->assertCount(4, $crawler->filter('th'));
    }

    public function testLinkPlaylist()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/playlists');
        
        $playlistLink = $crawler->filter('a[href*="/playlists/playlist/"]')->first();
        $this->assertCount(1, $playlistLink, "Aucun lien vers une playlist trouvé");
        
        $link = $playlistLink->attr('href');
        $crawler = $client->request('GET', $link);
        
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        
        $uri = $client->getRequest()->server->get("REQUEST_URI");
        $this->assertMatchesRegularExpression('#/playlists/playlist/\d+#', $uri, "L'URI ne correspond pas à une playlist");
    }
    
    public function testFiltrePlaylist()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/playlists');
        
        $crawler = $client->submitForm('filtrer', [
            'recherche' => 'Cours'
        ]);
        
        $playlistsTrouvees = $crawler->filter('h5')->count();
        $this->assertGreaterThan(0, $playlistsTrouvees, "Aucune playlist trouvée avec le filtre 'Cours'");
        $this->assertSelectorTextContains('h5', 'Cours');
    }

    public function testSortPlaylist()
    {
        $client = static::createClient();
        
        // Tri par nom DESC
        $crawler = $client->request('GET', '/playlists/tri/name/DESC');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode(), "Erreur sur /playlists/tri/name/DESC");
        $this->assertSelectorTextContains('h5', 'Visual Studio 2019 et C#');
        
        // Tri par nombre de formations ASC - CORRECTION du texte attendu
        $crawler = $client->request('GET', '/playlists/tri/nbformations/ASC');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode(), "Erreur sur /playlists/tri/nbformations/ASC");
        $this->assertSelectorTextContains('h5', 'Cours de programmation objet');  // CORRECTION
        
        // Tri par nombre de formations DESC
        $crawler = $client->request('GET', '/playlists/tri/nbformations/DESC');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode(), "Erreur sur /playlists/tri/nbformations/DESC");
        $this->assertSelectorTextContains('h5', 'Bases de la programmation (C#)');
    }
}