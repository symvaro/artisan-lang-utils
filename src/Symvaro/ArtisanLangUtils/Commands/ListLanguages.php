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
        $languageRepository = new LangRepository();

        $languages = $languageRepository->getLanguages();
        $defaultLanguage = $languageRepository->getDefaultLanguage();

        $languages
            ->filter(function ($language) use ($defaultLanguage) {
                return $language !== $defaultLanguage;
            })
            ->prepend($defaultLanguage);

        foreach ($languages as $language) {
            $this->info($language);
        }
    }
}