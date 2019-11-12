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
        $this->entries = $this->fetchEntries($uri);
    }

    static private function fetchEntries($uri)
    {
        // ob_start and the clean afterwards makes sure, that there won't be any output upon require.
        // Otherwise this can result in unwanted output, when e.g. something is written before the
        // <?php tag. I happened to have a lang file, which had an empty line before the php tag. Which
        // broke the PO format in the end.
        ob_start();
        $fileContents = require $uri;
        ob_clean();

        return Collection::make(Arr::dot($fileContents))->getIterator();
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