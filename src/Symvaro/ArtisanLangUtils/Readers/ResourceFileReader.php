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

    public function __construct($uri)
    {
        $this->filesystem = new Filesystem();

        $fileContents = $this->filesystem->getRequire($uri);

        $this->entries = Collection::make(Arr::dot($fileContents))->getIterator();
    }

    /**
     * @return \Symvaro\ArtisanLangUtils\Entry | null
     */
    protected function nextEntry()
    {
        if (!$this->entries->valid()) {
            return null;
        }

        $entry = new Entry($this->entries->key(), $this->entries->current());

        $this->entries->next();

        return $entry;
    }

    /**
     * Resets the reader, so that nextEntry will read the first entry again.
     * @return void
     */
    protected function reset()
    {
        $this->entries->rewind();
    }
}