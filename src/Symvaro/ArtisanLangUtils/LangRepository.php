<?php


namespace Symvaro\ArtisanLangUtils;

use App;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LangRepository
{
    public function getLocales(): Collection
    {
        $langPath = App::langPath();
        $langFiles = glob("$langPath/*");

        $languages = collect($langFiles)
            ->map(function ($p)  {
                $langFile = basename($p);

                if (Str::endsWith($langFile, '.json')) {
                    $langFile = substr($langFile, 0, strlen($langFile) - strlen('.json'));
                }

                return $langFile;
            })
            ->unique()
            ->filter(function ($p) {
                return $p !== 'vendor';
            })
            ->sort();

        return $languages;
    }

    public function getPathToLocale($locale)
    {
        return App::langPath() . "/$locale";
    }
}