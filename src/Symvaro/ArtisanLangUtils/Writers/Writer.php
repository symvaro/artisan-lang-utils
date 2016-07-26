<?php

namespace Symvaro\ArtisanLangUtils\Writers;

abstract class Writer
{
    protected $handle;

    function open($uri)
    {
        $this->handle = fopen($uri, 'w');
    }

    public abstract function write($key, $message = "");
    
    function close()
    {
        fclose($this->handle);
    }
}