<?php
namespace App\Tests\Validation;

use App\Entity\Formation;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FormationValidationTest extends KernelTestCase {
    
    /**
     * Création d'un objet de type Formation, avec informations minimales
     * @return Formation
     */
    public function getFormation(): Formation {
        return (new Formation())
                ->setTitle("Formation test");
    }
    
    /**
     * Utilisation du Kernel pour tester une règle de validation
     * @param Formation $formation
     * @param int $nbErreursAttendues
     * @param string $message
     */
    public function assertErrors(Formation $formation, int $nbErreursAttendues, string $message = "") {
        self::bootKernel();
        $validator = self::getContainer()->get(ValidatorInterface::class);
        $error = $validator->validate($formation);
        $this->assertCount($nbErreursAttendues, $error, $message);
    }
    
    public function testValidDateFormation() {
        // Dates dans le passé (valides car avant aujourd'hui 14/02/2026)
        $this->assertErrors($this->getFormation()->setPublishedAt(new \DateTime('yesterday')), 0, "Date d'hier (13/02/2026) devrait réussir");
        $this->assertErrors($this->getFormation()->setPublishedAt(new \DateTime("first day of January 2008")), 0, "01/01/2008 devrait réussir");
        $this->assertErrors($this->getFormation()->setPublishedAt(new \DateTime("last sat of July 2008")), 0, "26/07/2008 devrait réussir");
        // Ajout d'un test avec une date ancienne valide
        $this->assertErrors($this->getFormation()->setPublishedAt(new \DateTime("2020-01-01")), 0, "01/01/2020 devrait réussir");
    }
    
    public function testNonValidDateFormation() {
        // Dates dans le futur par rapport à aujourd'hui (14/02/2026)
        $this->assertErrors($this->getFormation()->setPublishedAt(new \DateTime('tomorrow')), 1, "Date de demain (15/02/2026) devrait échouer");
        $this->assertErrors($this->getFormation()->setPublishedAt(new \DateTime('+1 week')), 1, "Date dans 1 semaine devrait échouer");
        $this->assertErrors($this->getFormation()->setPublishedAt(new \DateTime('06/08/2026')), 1, "06/08/2026 devrait échouer");
        $this->assertErrors($this->getFormation()->setPublishedAt(new \DateTime('11/02/2027')), 1, "11/02/2027 devrait échouer");
        $this->assertErrors($this->getFormation()->setPublishedAt(new \DateTime('06/11/2028')), 1, "06/11/2028 devrait échouer");
        $this->assertErrors($this->getFormation()->setPublishedAt(new \DateTime('09/07/2029')), 1, "09/07/2029 devrait échouer");
    }
}