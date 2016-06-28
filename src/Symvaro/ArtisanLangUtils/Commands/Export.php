<?php

namespace Symvaro\ArtisanLangUtils\Commands;

use App;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symvaro\ArtisanLangUtils\Writers\POWriter;

class Export extends Command
{
    protected $signature =
        'lang:export {out} 
        {--f|format= : Export format. Default po or pot if no language is specified}
        {--l|lang= : Language key to export.}';

    protected $description = 'Export resources into lang files';

    private $writer;
    private $filesystem;

    public function handle()
    {
        if ($this->option('lang') == null) {
            $this->comment('NOT SUPPORTED YET: Please select a language');

            return;
        }

        if (!$this->validateLanguagePath()) {
            $this->comment('Could not find given language resource directory: "' .
                App::langPath() . '/' . $this->option('lang')
                . '"'
            );

            return;
        }

        $this->writer = new POWriter();
        $this->filesystem = new Filesystem();
        $this->exportLanguage();
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
        $language = $this->option('lang');

        if ($language == null) {
            return false;
        }

        return realpath(App::langPath() . '/' . $language);
    }

    private function exportLanguage($workingDir = '')
    {
        dump("export : ");
        $path = $this->getLangPath();
        
        if ($workingDir !== '') {
            $path .= '/' . $workingDir;
        }

        foreach ($this->filesystem->files($path) as $file) {
            $relativeFile = substr($file, strlen($path));
            
            $this->writeStringsOfFile($path, $relativeFile, $workingDir);
        }
        
    }
    
    private function writeStringsOfFile($dir, $file, $workingDir) 
    {
        $keyPrefix = $workingDir;
        
        if ($keyPrefix !== '') {
            $keyPrefix .= '/';
        }

        $keyPrefix .= substr($file, 0, strlen($file) - strlen('.php'));
        $keyPrefix .= '/';
        
        $langKeys = $this->filesystem->getRequire($dir . $file);
        
        dump([$dir, $file, $keyPrefix]);
        dump($langKeys);
    }
}