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
    private $language;

    private $filesystem;

    private $files;
    private $currentFilePos;
    private $currentFilePrefix;

    private $currentFileReader;

    public function __construct($uri)
    {
        $this->filesystem = new Filesystem();

        if (!$this->filesystem->isDirectory($uri)) {
            $uri = resource_path('lang/' . $uri);
        }

        if (!$this->filesystem->isDirectory($uri)) {
            throw new Exception('please specify the resource lang dir');
        }

        $this->langDirPath = $uri;

        $this->language = Arr::last(explode('/', $uri));

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


        $langDirPathStrlen = strlen($this->langDirPath) - strlen($this->language);

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
        $entry = null;

        while ($this->currentFileReader !== null && $entry == null) {
            if (!$this->currentFileReader->valid()) {
                $this->loadNextFile();
                continue;
            }

            $value = $this->currentFileReader->current();

            if (is_string($value)) {
                $entry = new Entry($this->currentFilePrefix . '.' . $this->currentFileReader->key(), $value);
            }

            $this->currentFileReader->next();
        }

        return $entry;
    }
}
