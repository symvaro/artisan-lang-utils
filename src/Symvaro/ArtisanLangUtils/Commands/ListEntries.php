<?php


namespace Symvaro\ArtisanLangUtils\Commands;

use Illuminate\Console\Command;
use Symvaro\ArtisanLangUtils\Factory;
use Symvaro\ArtisanLangUtils\Readers\POReader;

class ListEntries extends Command
{
    protected $signature =
        'lang:list {input}';

    protected $description = 'List all entries of a .po language file.';

    public function handle()
    {
        $reader = Factory::createReader($this->argument('input'));

        $reader->rewind();

        while($reader->valid()) {
            $next = $reader->currentEntry();

            echo "$next\n";

            $reader->next();
        }

        $reader->close();
    }
}
