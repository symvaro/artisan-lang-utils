<?php


namespace Symvaro\ArtisanLangUtils\Readers;


use Symvaro\ArtisanLangUtils\Entry;

class CsvReader implements Reader
{
    private $handle;

    public function open($uri)
    {
        $this->handle = fopen($uri, 'r');
    }

    public function close()
    {
        fclose($this->handle);
    }

    /**
     * @return \Symvaro\ArtisanLangUtils\Entry | null
     */
    public function next()
    {
        $next = fgetcsv($this->handle);

        if ($next === false) {
            return null;
        }

        return new Entry($next[0], $next[1]);
    }
}