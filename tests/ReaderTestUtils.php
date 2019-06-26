<?php

namespace Tests;

use Symvaro\ArtisanLangUtils\Readers\Reader;

trait ReaderTestUtils
{

    private function assertReaderEquals(Reader $r1, Reader $r2)
    {
        $this->assertEquals($r1->allMessages()->toArray(), $r2->allMessages()->toArray());
    }
}