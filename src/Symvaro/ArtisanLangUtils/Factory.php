<?php


namespace Symvaro\ArtisanLangUtils;


use Symvaro\ArtisanLangUtils\Readers\POReader;
use Symvaro\ArtisanLangUtils\Readers\ResourceDirReader;
use Symvaro\ArtisanLangUtils\Writers\POWriter;
use Symvaro\ArtisanLangUtils\Writers\ResourceWriter;

class Factory
{
    const READERS = [
        'po' => POReader::class,
        'resource' => ResourceDirReader::class
    ];

    const WRITERS = [
        'po' => POWriter::class,
        'resource' => ResourceWriter::class
    ];

    public static function createWriter($format, $output)
    {
        if (!isset(self::WRITERS[$format])) {
            return null;
        }

        $class = self::WRITERS[$format];

        return new $class($output);
    }

    public static function createReader($config)
    {
        $readerIndex = self::extractKind($config);

        $class = isset(self::READERS[$readerIndex]) ? self::READERS[$readerIndex] : ResourceDirReader::class;

        return new $class(self::extractValue($config));
    }

    private static function extractKind($config)
    {
        return substr($config, 0, strpos($config, ':'));
    }

    private static function extractValue($config)
    {
        return substr($config, strpos($config, ':') + 1);
    }

}