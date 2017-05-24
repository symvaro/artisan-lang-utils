<?php

namespace Symvaro\ArtisanLangUtils\Commands;

use App;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Symvaro\ArtisanLangUtils\Factory;
use Symvaro\ArtisanLangUtils\Readers\ResourceDirReader;
use Symvaro\ArtisanLangUtils\Writers\POWriter;

class Export extends Command
{
    protected $signature =
        'lang:export 
        {language : Language in lang resource directory}
        {output : File resource like "po:lang.po"}';

    protected $description = 'Export language resources into common lang file formats';

    private $writer;
    private $filesystem;
    
    public function handle()
    {
        if (!$this->validateLanguagePath()) {
            $this->comment('Could not find given language resource directory: "' .
                App::langPath() . '/' . $this->argument('language')
                . '"'
            );

            return;
        }

        $this->writer = Factory::createWriter($this->argument('output'));

        $this->exportTranslations();

        $this->writer->close();
    }

    private function validateLanguagePath()
    {
        $langPath = App::langPath();
        $realPath = $this->getLangPath();

        if (!$realPath) {
            return false;
        }
        
        return strncmp($langPath, $realPath, strlen($langPath)) === 0;
    }

    private function getLangPath()
    {
        $language = $this->argument('language');

        if ($language == null) {
            return false;
        }

        return realpath(App::langPath() . '/' . $language);
    }

    private function exportTranslations()
    {
        $path = $this->getLangPath();

        $reader = new ResourceDirReader($path);
        $reader->rewind();

        while ($reader->valid()) {
            $this->writer->write($reader->currentEntry());

            $reader->next();
        }
    }
}