<?php


namespace Symvaro\ArtisanLangUtils\Readers;


use Symvaro\ArtisanLangUtils\Entry;

class CsvReader extends Reader
{
    private $handle;

    public function open($uri)
    {
        $this->handle = fopen($uri, 'r');
    }

    protected function reset()
    {
        rewind($this->handle);
    }

    public function close()
    {
        fclose($this->handle);
    }

    /**
     * @return \Symvaro\ArtisanLangUtils\Entry | null
     */
    protected function nextEntry()
    {
        $next = fgetcsv($this->handle);

        if ($next === false) {
            return null;
        }

        return new Entry($next[0], $next[1]);
    }
}