<?php

namespace Symvaro\ArtisanLangUtils\Commands;


use Illuminate\Console\Command;
use Symvaro\ArtisanLangUtils\Readers\POReader;
use Symvaro\ArtisanLangUtils\Writers\ResourceWriter;

class Import extends Command
{
    protected $signature =
        'lang:import 
        {--f|format=po : Input file format.}
        {--j|as-json : Input will only be written to the language json}
        {--p|path : Specifies that the language argument is a real path}
        {input-file}
        {language}';

    // TODO how to handle keys, that only can be written to json

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