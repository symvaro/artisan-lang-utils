<?php

namespace Symvaro\ArtisanLangUtils\Commands;


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
        {--f|format=tsv : Input file format.}
        {--j|json-only : Input will only be written to the language json}
        {--p|path : Specifies that the language argument is a real path}
        {--l|language=}
        {input-file? : File to read from, stdin will be used if none is specified}';

    protected $description = 'Imports and replaces language strings from various formats for one language.';

    public function handle()
    {
        if (empty($this->option('language'))) {
            $this->error('language required');
            return;
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

        $path = resource_path('lang/' . $this->option('language'));

        $writer = new ResourceWriter();
        
        if ($this->option('json-only')) {
            $writer->outputJsonOnly();
        }
        
        $writer->open($path);

        foreach ($reader as $entry) {
            $writer->write($entry);
        }

        $writer->close();
        $reader->close();
    }

    private function errorUnknownFormat()
    {
        $error = "Invalid format! Available formats:";

        foreach (Factory::READERS as $key => $value) {
            $error .= "\n  $key";
        }

        $this->info($error);
    }
}