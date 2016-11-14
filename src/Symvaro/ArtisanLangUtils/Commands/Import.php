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
        $reader = new POReader();
        $writer = new ResourceWriter();

        $reader->open($this->argument('file'));

        $path = rtrim($this->argument('lang_path'), '/');

        $writer->open($path);

        while (($next = $reader->next()) !== null) {
            $writer->write($next);
        }

        $writer->close();
        $reader->close();
    }
}