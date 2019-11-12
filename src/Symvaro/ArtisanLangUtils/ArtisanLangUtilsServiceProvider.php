<?php
namespace Symvaro\ArtisanLangUtils;

use Illuminate\Support\ServiceProvider;
use Symvaro\ArtisanLangUtils\Commands\Export;
use Symvaro\ArtisanLangUtils\Commands\Import;
use Symvaro\ArtisanLangUtils\Commands\ListEntries;
use Symvaro\ArtisanLangUtils\Commands\ListLanguages;

class ArtisanLangUtilsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands(Export::class);
        $this->commands(Import::class);
        $this->commands(ListLanguages::class);
    }
}

