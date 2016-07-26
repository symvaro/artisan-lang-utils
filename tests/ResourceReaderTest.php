<?php

use PHPUnit\Framework\TestCase;

class ResourceReaderTest extends TestCase
{
    public function testThis()
    {
        $r = new \Symvaro\ArtisanLangUtils\Readers\ResourceReader();

        $r->open(__DIR__ . '/resources/lang/de');

        while (true) {
            $next = $r->next();

            if ($next === null) {
                break;
            }

            dump($next);
        }
    }
}