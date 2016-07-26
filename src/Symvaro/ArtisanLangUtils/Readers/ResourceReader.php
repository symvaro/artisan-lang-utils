<?php

namespace Symvaro\ArtisanLangUtils\Readers;

use Illuminate\Filesystem\Filesystem;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Symvaro\ArtisanLangUtils\Entry;

class ResourceReader implements Reader
{
    private $langDirPath;

    private $filesystem;

    private $files;
    private $currentFilePos;
    private $currentFilePrefix;

    private $entries;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    public function open($uri)
    {
        if (!$this->filesystem->isDirectory($uri)) {
            throw new Exception('please specify the resource lang dir');
        }

        $this->langDirPath = $uri;

        $this->files = $this->filesystem->allFiles($uri);
        $this->currentFilePos = 0;

        $this->loadNextFile();
    }

    public function close()
    {
    }

    private function loadNextFile()
    {
        if (!isset($this->files[$this->currentFilePos])) {
            $this->entries = null;
            return;
        }

        $nextFilePath = $this->files[$this->currentFilePos]->getRealPath();
        $fileContents = $this->filesystem->getRequire($nextFilePath);

        $this->entries = Collection::make(Arr::dot($fileContents))->getIterator();
        $this->entries->rewind();

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
    public function next()
    {
        if ($this->entries === null) {
            return null;
        }

        if (!$this->entries->valid()) {
            $this->loadNextFile();

            return $this->next();
        }

        $entry = new Entry($this->currentFilePrefix . '.' . $this->entries->key(), $this->entries->current());

        $this->entries->next();

        return $entry;
    }
}
