<?php

namespace Tests;

use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Symvaro\ArtisanLangUtils\Entry;
use Symvaro\ArtisanLangUtils\Readers\ResourceReader;
use Symvaro\ArtisanLangUtils\Writers\ResourceWriter;

class ResourceReaderWriterTest extends TestCase
{
    use ReaderTestUtils;

    public function testRead()
    {
        $r = new ResourceReader(__DIR__ . '/resources/lang/de');

        $tmp = tmpfile();

        foreach ($r as $key => $message) {
            fputcsv($tmp, [$key, $message]);
        }

        rewind($tmp);
        $this->assertEquals(file_get_contents(__DIR__ . '/resources/de.csv'), stream_get_contents($tmp));
    }

    public function testWrite()
    {
        $faker = Factory::create();
        $tmpDirName = sys_get_temp_dir() . '/lang_test_' . $faker->uuid;

        mkdir($tmpDirName);

        $sourceUri = __DIR__ . '/resources/lang/de';

        $r = new ResourceReader($sourceUri);
        $w = new ResourceWriter();

        $w->open($tmpDirName);

        $w->writeAll($r);

        $w->close();
        $r->close();

        $this->assertReaderEquals(new ResourceReader($sourceUri), new ResourceReader($tmpDirName));

        dump((new ResourceReader($sourceUri))->readAll()->toArray());
    }
}