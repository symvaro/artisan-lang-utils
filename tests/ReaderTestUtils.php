<?php

namespace Tests;

use Symvaro\ArtisanLangUtils\Readers\Reader;

trait ReaderTestUtils
{
    private function assertReaderFilesEquals($uri1, $readerClass1, $uri2, $readerClass2)
    {
        $r1 = new $readerClass1;
        $r2 = new $readerClass2;

        $r1->open($uri1);
        $r2->open($uri2);

        $this->assertReaderEquals($r1, $r2);
    }

    private function assertReaderEquals(Reader $r1, Reader $r2)
    {
        $a1 = $this->readerToArray($r1);
        $a2 = $this->readerToArray($r2);

        $this->assertEquals($a1, $a2);
    }

    private function readerToArray(Reader $r)
    {
        $a = [];

        while (($next = $r->next()) !== null) {
            $a[$next->getKey()] = $next->getMessage();
        }

        return $a;
    }
}