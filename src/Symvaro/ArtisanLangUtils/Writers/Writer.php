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