<?php


namespace Symvaro\ArtisanLangUtils;


use Symvaro\ArtisanLangUtils\Readers\JSONReader;
use Symvaro\ArtisanLangUtils\Readers\POReader;
use Symvaro\ArtisanLangUtils\Readers\Reader;
use Symvaro\ArtisanLangUtils\Readers\ResourceDirReader;
use Symvaro\ArtisanLangUtils\Writers\JSONWriter;
use Symvaro\ArtisanLangUtils\Writers\POWriter;
use Symvaro\ArtisanLangUtils\Writers\ResourceWriter;
use Symvaro\ArtisanLangUtils\Writers\Writer;

class Factory
{
    const READERS = [
        'po' => POReader::class,
        'resource' => ResourceDirReader::class,
        'json' => JSONReader::class,
    ];

    const WRITERS = [
        'po' => POWriter::class,
        'resource' => ResourceWriter::class,
        'json' => JSONWriter::class,
    ];

    /**
     * @param $format
     * @return Writer|null
     */
    public static function createWriter($format)
    {
        if (!isset(self::WRITERS[$format])) {
            return null;
        }

        $class = self::WRITERS[$format];

        return new $class();
    }

    /**
     * @param $format
     * @return Reader|null
     */
    public static function createReader($format)
    {
        if (!isset(self::READERS[$format])) {
            return null;
        }

        $class = self::READERS[$format];

        return new $class();
    }
}