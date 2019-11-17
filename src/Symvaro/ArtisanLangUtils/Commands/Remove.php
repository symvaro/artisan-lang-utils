<?php


namespace Symvaro\ArtisanLangUtils\Commands;


use Illuminate\Console\Command;
use Symvaro\ArtisanLangUtils\LangRepository;
use Symvaro\ArtisanLangUtils\Readers\ResourceDirReader;
use Symvaro\ArtisanLangUtils\Writers\ResourceWriter;

class Remove extends Command
{

    protected $signature = 'lang:remove {key? : If no key is specified, read keys from stdin}';

    protected $description = 'Removes a language key from all languages.';

    /** @var LangRepository */
    protected $langRepository;

    public function handle()
    {
        $this->langRepository = new LangRepository();

        $key = $this->argument('key');
        $keys = $key ? [$key] : $this->loadKeysFromStdin();

        $locales = $this->langRepository->getLanguages();

        foreach ($locales as $locale) {
            $this->removeKeysFromLanguage($locale, $keys);
        }
    }

    private function loadKeysFromStdin(): array
    {
        $handle = fopen('php://stdin', 'r');
        $keys = [];
        $line = null;

        while ($line !== false) {
            $line = fgets($handle);
            
            if (empty($line) && $line !== '0') {
                continue;
            }
            
            $keys[] = trim($line);
        }

        fclose($handle);
        
        return $keys;
    }

    private function removeKeysFromLanguage(string $language, array $keys)
    {
        $path = $this->langRepository->getPathToLanguage($language);

        $reader = new ResourceDirReader();
        $reader->open($path);
        $entries = $reader->allEntries();
        $reader->close();

        $entries = $entries->diffKeys(array_combine($keys, $keys));

        $writer = new ResourceWriter();
        $writer->open($path);
        $writer->writeAll($entries);
        $writer->close();
    }

}