<?php

namespace Tests;

use Symvaro\ArtisanLangUtils\Readers\Reader;

trait ReaderTestUtils
{

    private function assertReaderEquals(Reader $r1, Reader $r2)
    {
        $this->assertEquals($r1->readAll()->toArray(), $r2->readAll()->toArray());
    }
}