<?php


namespace Symvaro\ArtisanLangUtils;


use Symvaro\ArtisanLangUtils\Readers\POReader;
use Symvaro\ArtisanLangUtils\Readers\ResourceDirReader;
use Symvaro\ArtisanLangUtils\Writers\POWriter;
use Symvaro\ArtisanLangUtils\Writers\ResourceWriter;

class Factory
{
    public static function createWriter($config)
    {
        $class = [
            'po' => POWriter::class,
            'resource' => ResourceWriter::class
        ][self::extractKind($config)];

        return new $class(self::extractValue($config));
    }

    public static function createReader($config)
    {
        $class = [
            'po' => POReader::class,
            'resource' => ResourceDirReader::class
        ][self::extractKind($config)];

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