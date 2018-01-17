<?php

namespace Symvaro\ArtisanLangUtils\Writers;

use Symvaro\ArtisanLangUtils\Entry;
use Symvaro\ArtisanLangUtils\StringCollection;

abstract class Writer
{
    public function __construct($uri = null)
    {
        if ($uri !== null) {
            $this->open($uri);
        }
    }

    public abstract function open($uri);

    public abstract function write(Entry $entry);

    /**
     * @param StringCollection|\Traversable $strings
     */
    public function writeAll($strings)
    {
        foreach ($strings as $key => $message) {
            $this->write(new Entry($key, $message));
        }
    }

    public function close()
    {
        //
    }
}