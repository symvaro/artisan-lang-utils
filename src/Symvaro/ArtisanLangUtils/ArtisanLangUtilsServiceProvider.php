<?php
namespace Symvaro\ArtisanLangUtils;

use Illuminate\Support\ServiceProvider;
use Symvaro\ArtisanLangUtils\Commands\Export;
use Symvaro\ArtisanLangUtils\Commands\Import;
use Symvaro\ArtisanLangUtils\Commands\ListEntries;
use Symvaro\ArtisanLangUtils\Commands\ListLanguages;
use Symvaro\ArtisanLangUtils\Commands\Add;
use Symvaro\ArtisanLangUtils\Commands\Remove;

class ArtisanLangUtilsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands(Export::class);
        $this->commands(Import::class);
        $this->commands(ListLanguages::class);
        $this->commands(Add::class);
        $this->commands(Remove::class);
    }
}

