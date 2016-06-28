<?php
namespace Symvaro\ArtisanLangUtils;

use Illuminate\Support\ServiceProvider;

class ArtisanLangUtilsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands('Symvaro\ArtisanLangUtils\Commands\Export');
    }
}

