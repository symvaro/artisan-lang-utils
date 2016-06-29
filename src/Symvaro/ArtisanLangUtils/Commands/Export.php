<?php

namespace Symvaro\ArtisanLangUtils\Commands;

use App;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Symvaro\ArtisanLangUtils\Writers\POWriter;

class Export extends Command
{
    protected $signature =
        'lang:export 
        {output : File or directory} 
        {--l|lang= : Language key to export.}';

    // {--f|format= : Export format. Default po or pot if no language is specified}
    
    protected $description = 'Export language resources into common lang file formats';

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
        
        $this->filesystem = new Filesystem();
        
        $outfile = $this->getOutFilename();

        $this->comment("Export translations to '$outfile'");       

        $this->writer = new POWriter();
        $this->writer->open($outfile);
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
        $language = $this->option('lang');

        if ($language == null) {
            return false;
        }

        return realpath(App::langPath() . '/' . $language);
    }

    private function getOutFilename()
    {
        $output = $this->argument('output');

        if ($this->filesystem->isFile($output)) {
            return $output;
        }

        if (!$this->filesystem->isDirectory($output)) {
            return null;
        }

        if (substr($output, -1) !== '/') {
            $output .= '/';
        }
        
        return $output . 'app_' . $this->option('lang') . '.po';
    }

    private function exportTranslations($workingDir = '')
    {
        $path = $this->getLangPath();
        
        if ($workingDir !== '') {
            $path .= $workingDir;
        }
        
        foreach ($this->filesystem->files($path) as $file) {
            $relativeFile = substr($file, strlen($path));
            
            $this->writeStringsOfFile($path, $relativeFile, $workingDir);
        }
        
        foreach ($this->filesystem->directories($path) as $dir) {
            $relativePath = substr($dir, strlen($path));

            $this->exportTranslations($relativePath);
        }
    }
    
    private function writeStringsOfFile($dir, $file, $workingDir) 
    {
        $keyPrefix = $workingDir;
        
        $keyPrefix .= substr($file, 0, strlen($file) - strlen('.php'));
        $keyPrefix .= '.';
        
        $langKeys = $this->filesystem->getRequire($dir . $file);
        
        foreach (Arr::dot($langKeys) as $key => $value) {
            $this->writer->write($keyPrefix . $key, $value);
        }
    }
}