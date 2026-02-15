<?php

use App\Entity\Formation;
use PHPUnit\Framework\TestCase;

class FormationTest extends TestCase 
{
    public function testGetPublishedAtString()
    {
        $Formation = new Formation ();
        $Formation->setTitle("Formation test ");
        $Formation->setPublishedAt( new \DateTime("2026-02-14"));
        $this->assertEquals("14/02/2026" ,$Formation->getPublishedAtString());
    }
}