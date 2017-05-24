<?php

namespace Symvaro\ArtisanLangUtils\Readers;

use Illuminate\Filesystem\Filesystem;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Symvaro\ArtisanLangUtils\Entry;

class ResourceDirReader extends Reader
{
    private $langDirPath;

    private $filesystem;

    private $files;
    private $currentFilePos;
    private $currentFilePrefix;

    private $currentFileReader;

    public function __construct($uri)
    {
        $this->filesystem = new Filesystem();

        if (!$this->filesystem->isDirectory($uri)) {
            throw new Exception('please specify the resource lang dir');
        }

        $this->langDirPath = $uri;

        $this->reset();
    }

    protected function reset()
    {
        $this->files = $this->filesystem->allFiles($this->langDirPath);
        $this->currentFilePos = 0;

        $this->loadNextFile();
    }

    private function loadNextFile()
    {
        if (!isset($this->files[$this->currentFilePos])) {
            $this->currentFileReader = null;
            return;
        }

        $nextFilePath = $this->files[$this->currentFilePos]->getRealPath();

        $this->currentFileReader = new ResourceFileReader($nextFilePath);
        $this->currentFileReader->rewind();


        $langDirPathStrlen = strlen($this->langDirPath);

        $this->currentFilePos += 1;
        $this->currentFilePrefix = substr(
            $nextFilePath,
            $langDirPathStrlen,
            strlen($nextFilePath) - $langDirPathStrlen - strlen('.php')
        );
    }

    /**
     * @return \Symvaro\ArtisanLangUtils\Entry | null
     */
    protected function nextEntry()
    {
        if ($this->currentFileReader === null) {
            return null;
        }

        if (!$this->currentFileReader->valid()) {
            $this->loadNextFile();

            return $this->nextEntry();
        }

        $entry = new Entry($this->currentFilePrefix . '.' . $this->currentFileReader->key(), $this->currentFileReader->current());

        $this->currentFileReader->next();

        return $entry;
    }
}
