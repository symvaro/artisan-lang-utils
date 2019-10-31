<?php

namespace Symvaro\ArtisanLangUtils\Commands;


use Illuminate\Console\Command;
use Symvaro\ArtisanLangUtils\Factory;
use Symvaro\ArtisanLangUtils\Readers\POReader;
use Symvaro\ArtisanLangUtils\Writers\JSONWriter;
use Symvaro\ArtisanLangUtils\Writers\ResourceWriter;

class Import extends Command
{
    protected $signature = 'lang:import 
        {--f|format=po : Input file format.}
        {--j|json-only : Input will only be written to the language json}
        {--p|path : Specifies that the language argument is a real path}
        {--l|language=}
        {input-file}';

    protected $description = 'Imports and replaces language strings from various formats for one language.';

    public function handle()
    {
        if (empty($this->option('language'))) {
            $this->error('language required');
            return;
        }
        
        $reader = Factory::createReader($this->option('format'));

        if (!$reader) {
            $this->errorUnknownFormat();
            return;
        }

        $reader->open($this->argument('input-file'));
        $reader->rewind();

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