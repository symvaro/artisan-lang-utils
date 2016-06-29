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
        $message = str_replace('"', '\\"', $message);

        fputs(
            $this->handle,
            "\n" .
            "msgid \"$key\"\n" .
            "msgstr \"$message\"\n"
        );
    }

    function close()
    {
        fclose($this->handle);
    }
}
