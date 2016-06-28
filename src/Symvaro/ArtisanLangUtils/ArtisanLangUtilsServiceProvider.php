<?php
namespace Symvaro\ArtisanLangUtils;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Command;

class TestCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'exportlol';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display an inspiring quote';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('comment');
    }
}

class ArtisanLangUtilsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('symvaro.command.lang.export', function($app) {

            return new TestCommand();
        });

        $this->commands('symvaro.command.lang.export');
    }
}

