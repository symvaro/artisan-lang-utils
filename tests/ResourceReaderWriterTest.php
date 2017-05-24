<?php

namespace Tests;

use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Symvaro\ArtisanLangUtils\Entry;
use Symvaro\ArtisanLangUtils\Readers\ResourceFileReader;
use Symvaro\ArtisanLangUtils\Readers\ResourceDirReader;
use Symvaro\ArtisanLangUtils\Writers\ResourceWriter;

class ResourceReaderWriterTest extends TestCase
{
    use ReaderTestUtils;

    public function testResourceFileReader()
    {
        $r = new ResourceFileReader(__DIR__ . '/resources/lang/de/small.php');

        $this->assertEquals([
            'one' => 'Eins',
            'two.half' => 'Zwei-ein-halb'
        ], $r->readAll()->all());
    }

    public function testRead()
    {
        $r = new ResourceDirReader(__DIR__ . '/resources/lang/de');

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

        $r = new ResourceDirReader($sourceUri);
        $w = new ResourceWriter();

        $w->open($tmpDirName);

        $w->writeAll($r);

        $w->close();
        $r->close();

        $this->assertReaderEquals(new ResourceDirReader($sourceUri), new ResourceDirReader($tmpDirName));
    }
}