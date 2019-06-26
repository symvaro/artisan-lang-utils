<?php


namespace Symvaro\ArtisanLangUtils\Readers;


use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Symvaro\ArtisanLangUtils\Entry;

class ResourceFileReader extends Reader
{
    private $filesystem;

    private $entries;

    public function open($uri)
    {
        $this->filesystem = new Filesystem();

        $fileContents = require $uri;

        $this->entries = Collection::make(Arr::dot($fileContents))->getIterator();
    }

    /**
     * @return Entry | null
     */
    protected function nextEntry()
    {
        if (!$this->entries->valid()) {
            return null;
        }

        $key = $this->entries->key();
        $message = $this->entries->current();
        $this->entries->next();

        if ($message === []) {
            return null;
        }

        return new Entry($key, $message);
    }

    protected function reset()
    {
        $this->entries->rewind();
    }
}