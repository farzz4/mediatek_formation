<?php

namespace App\Tests\Repository;

use App\Entity\Formation;
use Doctrine\ORM\EntityManagerInterface;  // Utiliser l'interface plutôt que la classe
use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FormationRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager = null;  // Permet d'être null initialement

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        
        // Récupération de l'entity manager via le service container
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    protected function recupRepository(): FormationRepository
    {
        // Vérification que l'entityManager n'est pas null
        if (!$this->entityManager) {
            $this->fail("EntityManager non initialisé");
        }
        
        return $this->entityManager->getRepository(Formation::class);
    }

    public function testNbFormation()
    {
        $repository = $this->recupRepository();
        $nbFormation = $repository->count([]);
        $this->assertEquals(237, $nbFormation);
    }

    public function newFormation(): Formation
    {
        $formation = (new Formation())
            ->setTitle("un titre")
            ->setDescription("Description")
            ->setPublishedAt(new \DateTime("yesterday"));
        return $formation;
    }

    public function testAddFormation()
    {
        $repository = $this->recupRepository();
        $formation = $this->newFormation();
        $nbFormation = $repository->count([]);

        $this->entityManager->persist($formation);
        $this->entityManager->flush();

        $this->assertEquals(
            $nbFormation + 1,
            $repository->count([]),
            "erreur lors de l'ajout"
        );
    }

    public function testSupprFormation()
    {
        $repository = $this->recupRepository();
        $nbFormation = $repository->count([]);
        
        // Chercher la formation ajoutée précédemment
        $formation = $repository->findOneBy(['title' => "un titre"]);
        
        // Si elle n'existe pas, on en crée une pour la supprimer
        if (!$formation) {
            $formation = $this->newFormation();
            $this->entityManager->persist($formation);
            $this->entityManager->flush();
            // Recompter après l'ajout
            $nbFormation = $repository->count([]);
        }
        
        $this->assertNotNull($formation, "Impossible de créer/trouver une formation pour le test");
        
        $this->entityManager->remove($formation);
        $this->entityManager->flush();
        
        $this->assertEquals(
            $nbFormation - 1,
            $repository->count([]),
            "Erreur lors de la suppression"
        );
    }

    protected function tearDown(): void
    {
        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
        
        parent::tearDown();
    }
}