<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Symvaro\ArtisanLangUtils\Readers\POReader;
use Symvaro\ArtisanLangUtils\Readers\ResourceDirReader;
use Symvaro\ArtisanLangUtils\Writers\POWriter;

class PoFormatTest extends TestCase
{
    public function testReadWrite()
    {
        $r = new ResourceDirReader();
        $r->open(__DIR__ . '/resources/lang/de');
        $entries = $r->allEntries();

        $poWriter = new POWriter();

        $tmp = fopen('php://memory', 'r+');
        $poWriter->openResource($tmp);
        $poWriter->writeAll($entries);
        
        rewind($tmp);

        $poReader = new POReader();
        $poReader->openResource($tmp);

        $this->assertEquals($entries->all(), $poReader->allEntries()->all());
    }

}