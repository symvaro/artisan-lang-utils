<?php

namespace Tests;

use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Symvaro\ArtisanLangUtils\Readers\ResourceReader;
use Symvaro\ArtisanLangUtils\Writers\ResourceWriter;

class ResourceReaderWriterTest extends TestCase
{
    use ReaderTestUtils;

    public function testRead()
    {
        $r = new ResourceReader();

        $r->open(__DIR__ . '/resources/lang/de');

        $tmp = tmpfile();

        while (true) {
            $next = $r->next();

            if ($next === null) {
                break;
            }

            fputcsv($tmp, [$next->getKey(), $next->getMessage()]);
        }

        rewind($tmp);
        $this->assertEquals(file_get_contents(__DIR__ . '/resources/de.csv'), stream_get_contents($tmp));
    }

    public function testWrite()
    {
        $faker = Factory::create();
        $tmpDirName = sys_get_temp_dir() . '/lang_test_' . $faker->uuid;

        dump($tmpDirName);
        mkdir($tmpDirName);

        $r = new ResourceReader();
        $w = new ResourceWriter();

        $sourceUri = __DIR__ . '/resources/lang/de';

        $r->open($sourceUri);
        $w->open($tmpDirName);

        while (true) {
            $next = $r->next();

            if ($next === null) {
                break;
            }

            $w->write($next);
        }

        $w->close();
        $r->close();

        $this->assertReaderFilesEquals($sourceUri, ResourceReader::class, $tmpDirName, ResourceReader::class);
    }
}