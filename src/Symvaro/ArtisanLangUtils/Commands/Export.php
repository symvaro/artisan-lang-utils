<?php

namespace Symvaro\ArtisanLangUtils\Commands;

use App;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Symvaro\ArtisanLangUtils\Factory;
use Symvaro\ArtisanLangUtils\Readers\ResourceDirReader;
use Symvaro\ArtisanLangUtils\Writers\POWriter;

class Export extends Command
{
    protected $signature =
        'lang:export 
        {--l|language= : Language in lang resource directory}
        {--p|path= : Path to file/folder}
        {--f|format=po : Output file format.}
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
        
        $writer = Factory::createWriter($this->option('format'));
        $writer->open($uri);

        if ($writer === null) {
            $this->errorUnknownFormat();
            return;
        }

        $this->exportTranslations($path, $writer);

        $writer->close();
    }

    private function errorUnknownFormat()
    {
        $error = "Invalid format! Available formats:";

        foreach (Factory::WRITERS as $key => $value) {
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