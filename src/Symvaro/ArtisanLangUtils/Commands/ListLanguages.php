<?php


namespace Symvaro\ArtisanLangUtils\Commands;

use App;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ListLanguages extends Command
{

    protected $signature = 'lang:list';

    protected $description = 'Lists all languages. Default language will be shown first';

    public function handle()
    {
        $defaultLocale = App::getLocale();

        $langPath = App::langPath();
        $langFiles = glob("$langPath/*");
        
        $langFiles = collect($langFiles)
            ->map(function ($p)  {
                $langFile = basename($p);

                if (Str::endsWith($langFile, '.json')) {
                    $langFile = substr($langFile, 0, strlen($langFile) - strlen('.json'));
                }

                return $langFile;
            })
            ->unique()
            ->filter(function ($p) use ($defaultLocale) {
                return $p !== 'vendor' && $p !== $defaultLocale;
            })
            ->sort()
            ->prepend($defaultLocale);

        foreach ($langFiles as $file) {
            $this->info($file);
        }
    }
}