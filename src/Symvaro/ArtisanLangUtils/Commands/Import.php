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
        $writer = new ResourceWriter();

        $path = rtrim($this->argument('lang_path'), '/');

        $writer->open($path);

        while (($next = $reader->nextEntry()) !== null) {
            $writer->write($next);
        }

        $writer->close();
        $reader->close();
    }
}