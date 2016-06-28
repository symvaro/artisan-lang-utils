<?php

namespace Symvaro\ArtisanLangUtils\Commands;

use App;
use Illuminate\Console\Command;

class Export extends Command
{
    protected $signature =
        'lang:export {out} 
        {--f|format= : Export format. Default po or pot if no language is specified}
        {--l|lang= : Language key to export.}';

    protected $description = 'Export resources into lang files';

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
        
        
    }
    
    private function validateLanguagePath()
    {
        $langPath = App::langPath();
        $realPath = $this->getLangPath();

        if (!$realPath) {
            return false;
        }

        dump($langPath, $realPath);

        return strncmp($langPath, $realPath, strlen($langPath)) === 0;
    }

    private function getLangPath() {
        $language = $this->option('lang');
        
        if ($language == null) {
            return false;
        }
        
        return realpath(App::langPath() . '/' . $language);
    }
}