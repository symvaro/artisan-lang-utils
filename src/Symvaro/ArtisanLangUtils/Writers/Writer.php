<?php

namespace Symvaro\ArtisanLangUtils\Writers;

use Symvaro\ArtisanLangUtils\Entry;

abstract class Writer
{
    public abstract function open($uri);

    public abstract function write(Entry $entry);

    public function close()
    {
        //
    }
}