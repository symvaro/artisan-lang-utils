<?php


namespace Symvaro\ArtisanLangUtils\Commands;

use Illuminate\Console\Command;
use Symvaro\ArtisanLangUtils\Factory;
use Symvaro\ArtisanLangUtils\Readers\POReader;
use Symvaro\ArtisanLangUtils\Readers\ResourceDirReader;

class ListEntries extends Command
{
    protected $signature =
        'lang:list {language}';

    protected $description = 'List all entries of a language.';

    public function handle()
    {
        $language = $this->argument('language');

        $reader = new ResourceDirReader();
        $reader->open($language);

        foreach ($reader as $entry) {
            echo "$entry\n";
        }

        $reader->close();
    }
}
