<?php

namespace Tests;

use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Symvaro\ArtisanLangUtils\Readers\ResourceFileReader;
use Symvaro\ArtisanLangUtils\Readers\ResourceDirReader;
use Symvaro\ArtisanLangUtils\Writers\ResourceWriter;
use Illuminate\Support\Str;

// TODO read with lang.json
// TODO write lang.json
// TODO test lang.json that has resource like strings
class ResourceReaderWriterTest extends TestCase
{
    use ReaderTestUtils;

    public function testResourceFileReader()
    {
        $r = new ResourceFileReader();
        $r->open(__DIR__ . '/resources/lang/de/small.php');

        $this->assertEquals([
            'one' => 'Eins',
            'two.half' => 'Zwei-ein-halb'
        ], $r->allMessages()->all());
    }

    public function testRead()
    {
        $r = new ResourceDirReader();
        $r->open(__DIR__ . '/resources/lang/de');

        $tmp = tmpfile();

        foreach ($r as $entry) {
            fputcsv($tmp, [$entry->key, $entry->message]);
        }

        rewind($tmp);
        $this->assertEquals(file_get_contents(__DIR__ . '/resources/de.csv'), stream_get_contents($tmp));
    }

    public function testJsonOnly()
    {
        $r = new ResourceDirReader();
        $r->open(__DIR__ . '/resources/lang/us');
        $values = $r->allMessages()->all();

        $this->assertEquals([
            'only one' => "I'm the only one."
        ], $values);

        $dir = $this->tmpDir();

        $w = new ResourceWriter();
        $w->open($dir);
        $w->writeAll($r->allEntries());
        $w->close();

        $this->assertEquals($values, json_decode(file_get_contents($dir . '.json'), JSON_OBJECT_AS_ARRAY));
        $this->assertDirIsEmpty($dir);
    }

    private function tmpDir()
    {
        $tmpDirName = sys_get_temp_dir() . '/lang_test_' . Str::random();

        mkdir($tmpDirName);

        return $tmpDirName;
    }

    private function assertDirIsEmpty($dir)
    {
        $this->assertTrue(count(scandir($dir)) == 2);
    }

    public function testWrite()
    {
        $faker = Factory::create();
        $tmpDirName = sys_get_temp_dir() . '/lang_test_' . $faker->uuid;

        mkdir($tmpDirName);

        $tmpDirName .= '/de';

        mkdir($tmpDirName);

        $sourceUri = __DIR__ . '/resources/lang/de';

        $r = new ResourceDirReader();
        $r->open($sourceUri);
        $w = new ResourceWriter();
        $w->open($tmpDirName);

        $w->writeAll($r);

        $w->close();
        $r->close();

        $r1 = new ResourceDirReader();
        $r1->open($sourceUri);
        $r2 = new ResourceDirReader();
        $r2->open($tmpDirName);

        $this->assertReaderEquals($r1, $r2);
    }

    public function testReuseFiles()
    {
        $faker = Factory::create();
        $tmpDirName = sys_get_temp_dir() . '/lang_test_' . $faker->uuid;
        $sourceUri = __DIR__ . '/resources/lang/de';

        exec("cp -r $sourceUri $tmpDirName");

        $r = new ResourceDirReader();
        $r->open($tmpDirName);
        $w = new ResourceWriter();
        $w->open($tmpDirName);

        $w->writeAll($r);

        $w->close();
        $r->close();

        $r1 = new ResourceDirReader();
        $r1->open($sourceUri);
        $r2 = new ResourceDirReader();
        $r2->open($tmpDirName);

        $this->assertReaderEquals($r1, $r2);
        $this->assertFalse(file_exists("$tmpDirName/subdir.php"));
    }
}