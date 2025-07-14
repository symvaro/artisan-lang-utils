<?php

namespace Symvaro\ArtisanLangUtils\Commands;

use Illuminate\Support\Facades\App;
use Illuminate\Console\Command;
use Symvaro\ArtisanLangUtils\LangRepository;
use Symvaro\ArtisanLangUtils\Readers\ResourceDirReader;
use Symvaro\ArtisanLangUtils\Writers\JSONWriter;
use Symvaro\ArtisanLangUtils\Writers\POWriter;
use Symvaro\ArtisanLangUtils\Writers\ResourceWriter;
use Symvaro\ArtisanLangUtils\Writers\TSVWriter;

class Export extends Command
{
    const WRITERS = [
        'tsv' => TSVWriter::class,
        'po' => POWriter::class,
        'resource' => ResourceWriter::class,
        'json' => JSONWriter::class,
    ];

    protected $signature =
        'lang:export 
        {--a|all : Export all strings of all languages. Not every format supports this method.}
        {--l|language= : Language in lang resource directory.}
        {--p|path= : Path to the language file/folder.}
        {--f|format=tsv : Output file format. Currently supported: tsv/po/json/resource whereas resource a folder like a laravel lang folder.}
        {--d|diff= : Output language strings that are missing in the language given by --diff, compared to the language given by --language or --path}
        {output-file? : File to write to. If not specified, stdout is used.}';

    protected $description = 'Export language resources into given lang file format. If no language or path is specified, the default language will be used';

    public function handle()
    {
        $path = $this->option('path');

        if ($this->option('all')) {
            return $this->all($path);
        }

        $language = $this->option('language');

        if (!blank($language) && !blank($path)) {
            $this->error("--language and --path can not be provided at the same time");
            return 1;
        }

        if ($path === null && $language === null) {
            $language = App::getLocale();
        }

        if ($language) {
            $path = App::langPath() . "/$language";
        }

        $uri = $this->argument('output-file');

        if ($uri == null) {
            $uri = 'php://output';
        }

        $writerClass = self::WRITERS[$this->option('format')] ?? null;

        if ($writerClass === null) {
            $this->errorUnknownFormat();
            return 1;
        }

        $diff = $this->option('diff');
        if ($diff) {
            $diffKeys = $this->loadDiffKeys(App::langPath() . "/$diff");
        }

        $writer = new $writerClass();
        $writer->open($uri);
        $this->exportTranslations($path, $writer, $diffKeys ?? []);
        $writer->close();

        return 0;
    }

    private function errorUnknownFormat()
    {
        $error = "Invalid format! Available formats:";

        foreach (self::WRITERS as $key => $value) {
            $error .= "\n  $key";
        }

        $this->info($error);
    }

    private function exportTranslations($path, $writer, $excludeKeys = [])
    {
        $reader = new ResourceDirReader();
        $reader->open($path);

        foreach ($reader as $entry) {
            if ($excludeKeys[$entry->getKey()] ?? false) {
                continue;
            }
            $writer->write($entry);
        }

        $reader->close();
    }

    private function loadDiffKeys($path)
    {
        $keys = [];
        $reader = new ResourceDirReader();
        $reader->open($path);
        foreach ($reader as $entry) {
            $keys[$entry->getKey()] = true;
        }
        $reader->close();

        return $keys;
    }

    private function all($path): int
    {
        if ($this->option('format') !== 'json') {
            $this->error('only json implemented for this mode');
            return 1;
        }

        $path = $path ?? App::langPath();

        $languageRepository = new LangRepository($path);
        $languages = $languageRepository->getLanguages();

        $allKeys = [];

        foreach ($languages as $lang) {
            $reader = new ResourceDirReader();
            $reader->open($languageRepository->getPathToLanguage($lang));

            foreach ($reader as $entry) {
                if (!isset($allKeys[$entry->key])) {
                    $allKeys[$entry->key] = [];
                }

                $allKeys[$entry->key][$lang] = $entry->message;
            }

            $reader->close();
        }

        echo json_encode($allKeys, JSON_PRETTY_PRINT);
        return 0;
    }

}
