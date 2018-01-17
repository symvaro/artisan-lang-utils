<?php

namespace Symvaro\ArtisanLangUtils\Commands;


use Illuminate\Console\Command;
use Symvaro\ArtisanLangUtils\Readers\POReader;
use Symvaro\ArtisanLangUtils\Writers\ResourceWriter;

class Import extends Command
{
    protected $signature =
        'lang:import 
        {file : po file} 
        {lang_path : e.g. ./resources/lang/en}';

    public function handle()
    {
        $reader = new POReader($this->argument('file'));
        $reader->rewind();


        $path = rtrim($this->argument('lang_path'), '/');

        $writer = new ResourceWriter();
        $writer->open($path);

        while ($reader->valid()) {
            $writer->write($reader->currentEntry());

            $reader->next();
        }

        $writer->close();
        $reader->close();
    }
}