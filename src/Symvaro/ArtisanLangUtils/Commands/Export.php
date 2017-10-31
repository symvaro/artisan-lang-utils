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
        {out-file? : File to write to. If not specified, stdout is used.}
        {--format=po : Output file format.}';

    protected $description = 'Export language resources into common lang file formats';

    private $writer;

    public function handle()
    {
        if (!$this->validateLanguagePath()) {
            $this->comment('Could not find given language resource directory: "' .
                App::langPath() . '/' . $this->argument('language')
                . '"'
            );

            return;
        }

        $uri = $this->argument('out-file');

        if ($uri == null) {
            $uri = 'php://output';
        }
        
        $this->writer = Factory::createWriter($this->option('format'), $uri);

        if ($this->writer === null) {
            $this->errorUnknownFormat();
            return;
        }

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

    private function errorUnknownFormat()
    {
        $error = "Invalid format! Available formats:";

        foreach (Factory::WRITERS as $key => $value) {
            $error .= "\n  $key";
        }

        $this->info($error);
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