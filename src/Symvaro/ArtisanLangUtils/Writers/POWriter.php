<?php

namespace Symvaro\ArtisanLangUtils\Writers;

class POWriter implements WriterContract
{
    private $handle;

    function open($uri)
    {
        $this->handle = fopen($uri, 'w');
    }

    function write($key, $message = "")
    {
        fputs(
            $this->handle,
            "msgid \"$key\"\n" .
            "msgstr \"$message\"\n"
        );
    }

    function close()
    {
        fclose($this->handle);
    }
}
