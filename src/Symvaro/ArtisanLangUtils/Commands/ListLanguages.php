<?php


namespace Symvaro\ArtisanLangUtils\Commands;

use App;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symvaro\ArtisanLangUtils\LangRepository;

class ListLanguages extends Command
{
    protected $signature = 'lang:list';

    protected $description = 'Lists all locales. Default locale will be shown first';

    public function handle()
    {
        $locales = (new LangRepository())->getLocales();
        $defaultLocale = App::getLocale();

        $locales
            ->filter(function ($locale) use ($defaultLocale) {
                return $locale !== $defaultLocale;
            })
            ->prepend($defaultLocale);

        foreach ($locales as $locale) {
            $this->info($locale);
        }
    }
}