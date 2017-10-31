<?php


namespace Symvaro\ArtisanLangUtils\Commands;

use Illuminate\Console\Command;
use Symvaro\ArtisanLangUtils\Factory;
use Symvaro\ArtisanLangUtils\Readers\POReader;
use Symvaro\ArtisanLangUtils\Readers\ResourceDirReader;

class ListEntries extends Command
{
    protected $signature =
        'lang:list {input}';

    protected $description = 'List all entries of a language.';

    public function handle()
    {
        $language = $this->argument('input');

        $reader = new ResourceDirReader($language);

        $reader->rewind();

        while($reader->valid()) {
            $next = $reader->currentEntry();

            echo "$next\n";

            $reader->next();
        }

        $reader->close();
    }
}
