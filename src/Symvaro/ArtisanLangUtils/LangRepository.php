<?php


namespace Symvaro\ArtisanLangUtils;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LangRepository
{
    private $path;

    public function __construct($path = null)
    {
        $this->path = $path ?? App::langPath();
    }

    public function getLanguages(): Collection
    {
        $langFiles = glob("$this->path/*");

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

    public function getDefaultLanguage()
    {
        return App::getLocale();
    }

    public function getFallbackLanguage()
    {
        return config('app.fallback_locale');
    }

    public function getPathToLanguage($locale)
    {
        return "$this->path/$locale";
    }
}