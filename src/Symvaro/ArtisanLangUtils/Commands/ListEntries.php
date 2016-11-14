<?php


namespace Symvaro\ArtisanLangUtils\Commands;

use Illuminate\Console\Command;
use Symvaro\ArtisanLangUtils\Readers\POReader;

class ListEntries extends Command
{
    protected $signature =
        'lang:list {file}';

    protected $description = 'List all entries of a .po language file.';

    public function handle()
    {
        $reader = new POReader();

        $reader->open($this->argument('file'));

        while (($next = $reader->next()) !== null) {
            echo "$next\n";
        }

        $reader->close();
    }
}
