<?php

use Symvaro\ArtisanLangUtils\Readers\JSONReader;

class JSONReaderTest extends \PHPUnit\Framework\TestCase
{
    public function testRead()
    {
        $jsonFile = __DIR__ . '/resources/lang/en.json';

        $r = new JSONReader();
        $r->open($jsonFile);

         $this->assertEquals(
             $r->allMessages()->toArray(),
             json_decode(file_get_contents($jsonFile), true)
         );
    }
}
