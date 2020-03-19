<?php


namespace Symvaro\ArtisanLangUtils\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symvaro\ArtisanLangUtils\Entry;
use Symvaro\ArtisanLangUtils\LangRepository;
use Symvaro\ArtisanLangUtils\Readers\ResourceDirReader;
use Symvaro\ArtisanLangUtils\Writers\ResourceWriter;

class Add extends Command
{
    protected $signature = 'lang:add
        {--l|language= : If no language is chosen, a message for the default and fallback language will be asked.}
        {key? : If no key is specified, key will be asked for.}';

    protected $description = 'Adds a message using your $EDITOR if set.';

    /** @var LangRepository */
    private $langRepository;

    public function handle()
    {
        $this->langRepository = new LangRepository();

        $key = $this->argument('key');
        $key = $key ?? $this->ask('Key');

        $language = $this->option('language');
        if (!$language) {
            $language = $this->langRepository->getDefaultLanguage();
            $fallbackLanguage = $this->langRepository->getFallbackLanguage();
        }

        $this->askForMessage($key, $language);

        if (isset($fallbackLanguage) && $fallbackLanguage !== $language) {
            $this->askForMessage($key, $fallbackLanguage);
        }

    }

    private function askForMessage($key, $language)
    {
        $editor = getenv('EDITOR');

        if ($editor) {
            $message = $this->getMessageUsingEditor($editor, $language);
        }
        else {
            $message = $this->ask('Message for ' . $language);
        }


        if (empty($message) && $message !== '0') {
            $this->info("Skipping $language...");
            return;
        }

        $this->addKeyToLanguage($language, new Entry($key, $message));
    }

    private function getMessageUsingEditor($editor, $language)
    {
        $tmp = tempnam('/tmp', "lang_{$language}_");
        system("$editor $tmp > `tty`");
        $message = file_get_contents($tmp);
        unlink($tmp);

        if (empty($message)) {
            return null;
        }

        // using an editor like here always adds an additional \n to end of string
        if (Str::endsWith($message, "\n")) {
            $message = substr($message, 0, strlen($message)-2);
        }

        return $message;
    }

    private function addKeyToLanguage(string $language, Entry $entry)
    {
        $path = $this->langRepository->getPathToLanguage($language);

        $reader = new ResourceDirReader();
        $reader->open($path);
        $entries = $reader->allEntries();
        $reader->close();

        $entries = $entries->add($entry);

        $writer = new ResourceWriter();
        $writer->open($path);
        $writer->writeAll($entries);
        $writer->close();
    }

}
