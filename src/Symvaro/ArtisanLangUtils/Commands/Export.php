<?php

namespace Symvaro\ArtisanLangUtils\Commands;

use App;
use Illuminate\Console\Command;
use Symvaro\ArtisanLangUtils\Readers\ResourceDirReader;
use Symvaro\ArtisanLangUtils\Writers\JSONWriter;
use Symvaro\ArtisanLangUtils\Writers\POWriter;
use Symvaro\ArtisanLangUtils\Writers\ResourceWriter;
use Symvaro\ArtisanLangUtils\Writers\TSVWriter;

class Export extends Command
{
    const WRITERS = [
        'tsv' => TSVWriter::class,
        'po' => POWriter::class,
        'resource' => ResourceWriter::class,
        'json' => JSONWriter::class,
    ];

    protected $signature =
        'lang:export 
        {--l|language= : Language in lang resource directory}
        {--p|path= : Path to file/folder}
        {--f|format=tsv : Output file format.}
        {output-file? : File to write to. If not specified, stdout is used.}';

    protected $description = 'Export language resources into given lang file format';

    public function handle()
    {
        $language = $this->option('language');
        $path = $this->option('path');

        if ((!empty($path) && !empty($language))
                || (empty($path) && empty($language))) {
            $this->error('Either language or path must be used.');
            return;
        }


        if ($language) {
            $path = App::langPath() . "/$language";
        }

        $uri = $this->argument('output-file');

        if ($uri == null) {
            $uri = 'php://output';
        }

        $writerClass = self::WRITERS[$this->option('format')] ?? null;

        if ($writerClass === null) {
            $this->errorUnknownFormat();
            return;
        }

        $writer = new $writerClass();
        $writer->open($uri);
        $this->exportTranslations($path, $writer);
        $writer->close();
    }

    private function errorUnknownFormat()
    {
        $error = "Invalid format! Available formats:";

        foreach (self::WRITERS as $key => $value) {
            $error .= "\n  $key";
        }

        $this->info($error);
    }

    private function exportTranslations($path, $writer)
    {
        $reader = new ResourceDirReader();
        $reader->open($path);

        foreach ($reader as $entry) {
            $writer->write($entry);
        }

        $reader->close();
    }
}