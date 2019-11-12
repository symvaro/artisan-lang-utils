<?php

namespace Symvaro\ArtisanLangUtils\Readers;

use Illuminate\Filesystem\Filesystem;
use Exception;
use Illuminate\Support\Arr;
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

    private $jsonParsed = false;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    public function open($uri)
    {
        if (!$this->filesystem->isDirectory($uri)) {

            $jsonPath = realpath($uri . '.json');

            if ($jsonPath === false) {
                $uri = resource_path('lang/' . $uri);
            }
        }

        $this->langDirPath = $uri;

        $this->language = Arr::last(explode('/', $uri));

        $this->reset();
    }

    protected function reset()
    {
        $this->currentFileReader = new ResourceFileReader();

        if ($this->filesystem->isDirectory($this->langDirPath)) {
            $this->files = $this->filesystem->allFiles($this->langDirPath);
        }
        else {
            $this->files = [];
        }

        $this->currentFilePos = 0;
        $this->jsonParsed = false;

        $this->loadNextFile();
    }

    private function loadNextFile()
    {
        if (!isset($this->files[$this->currentFilePos])) {
            $this->currentFileReader = $this->tryLoadJson();
            return;
        }

        $nextFilePath = $this->files[$this->currentFilePos]->getRealPath();

        $this->currentFileReader->open($nextFilePath);
        $this->currentFileReader->rewind();

        $langDirPathStrlen = strlen($this->langDirPath) + 1;

        $this->currentFilePos += 1;
        $this->currentFilePrefix = str_replace('/', '.', substr(
                $nextFilePath,
                $langDirPathStrlen,
                strlen($nextFilePath) - $langDirPathStrlen - strlen('.php')
            )) . '.';
    }

    private function tryLoadJson()
    {
        if ($this->jsonParsed) {
            return null;
        }

        $this->jsonParsed = true;

        $jsonPath = $this->langDirPath . '.json';

        if (realpath($jsonPath) === false) {
            return null;
        }

        $reader = new JSONReader();
        $reader->open($jsonPath);
        $reader->rewind();

        $this->currentFilePrefix = '';

        return $reader;
    }

    /**
     * @return Entry | null
     */
    protected function nextEntry()
    {
        $entry = null;

        while ($this->currentFileReader !== null && $entry == null) {
            if (!$this->currentFileReader->valid()) {
                $this->loadNextFile();
                continue;
            }

            $entry = $this->currentFileReader->current();
            $entry->key = $this->currentFilePrefix . $entry->key;

            $this->currentFileReader->next();
        }

        return $entry;
    }
}
