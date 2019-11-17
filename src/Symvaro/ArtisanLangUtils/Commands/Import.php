<?php

namespace Symvaro\ArtisanLangUtils\Commands;


use App;
use Illuminate\Console\Command;
use Symvaro\ArtisanLangUtils\Readers\JSONReader;
use Symvaro\ArtisanLangUtils\Readers\POReader;
use Symvaro\ArtisanLangUtils\Readers\ResourceDirReader;
use Symvaro\ArtisanLangUtils\Readers\TSVReader;
use Symvaro\ArtisanLangUtils\Writers\ResourceWriter;

class Import extends Command
{
    const READERS = [
        'tsv' => TSVReader::class,
        'po' => POReader::class,
        'resource' => ResourceDirReader::class,
        'json' => JSONReader::class,
    ];
    
    protected $signature = 'lang:import 
        {--l|language=}
        {--p|path : Path to the language folder}
        {--f|format=tsv : Input file format. Currently supported: tsv/po/json/resource whereas resource a folder like a laravel lang folder.}
        {--j|json-only : Input will only be written to the language json}
        {--replace-all : Deletes also those, that are not present in input}
        {input-file? : File to read from, stdin will be used if none is specified}';

    protected $description = 'Imports and replaces language strings from various formats for one language.';

    public function handle()
    {
        $path = $this->option('path');

        if (empty($path)) {
            $language = $this->option('language');
            if (empty($language)) {
                $language = App::getLocale();
            }
            $path = App::langPath() . "/$language";
        }

        $readerClass = self::READERS[$this->option('format')] ?? null;

        if (!$readerClass) {
            $this->errorUnknownFormat();
            return;
        }

        $reader = new $readerClass();

        $inFile = $this->argument('input-file');
        
        if (empty($inFile)) {
            $inFile = 'php://stdin';
        }

        $reader->open($inFile);
        $entries = iterator_to_array($reader);
        $reader->close();
        
        if (!$this->option('replace-all')) {
            $entries = $this->merge($entries, $path);
        }

        $writer = new ResourceWriter();
        
        if ($this->option('json-only')) {
            $writer->outputJsonOnly();
        }
        
        $writer->open($path);

        foreach ($entries as $entry) {
            $writer->write($entry);
        }

        $writer->close();
    }

    private function merge($entries, $langPath)
    {
        $reader = new ResourceDirReader();
        $reader->open($langPath);
        $currentEntries = iterator_to_array($reader);
        $reader->close();

        return array_merge($currentEntries, $entries);
    }

    private function errorUnknownFormat()
    {
        $error = "Invalid format! Available formats:";

        foreach (self::READERS as $key => $value) {
            $error .= "\n  $key";
        }

        $this->info($error);
    }
}