<?php

namespace Symvaro\ArtisanLangUtils\Writers;

use Symvaro\ArtisanLangUtils\Entry;
use Symvaro\ArtisanLangUtils\StringCollection;

abstract class Writer
{
    public abstract function open($uri);

    public abstract function write(Entry $entry);

    public function writeAll($entries)
    {
        foreach ($entries as $entry) {
            $this->write($entry);
        }
    }

    public function close()
    {
        //
    }
}