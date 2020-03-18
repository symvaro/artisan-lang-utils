<?php

namespace Symvaro\ArtisanLangUtils\Writers;

use Symvaro\ArtisanLangUtils\Entry;

class POWriter extends Writer
{
    private $handle;

    function open($uri)
    {
        $this->handle = fopen($uri, 'w');
    }

    function openResource($handle)
    {
        $this->handle = $handle;
    }

    function write(Entry $entry)
    {
        $message = str_replace('"', '\\"', $entry->getMessage());
        $message = str_replace("\n", "\\n\"\n\"", $message);
        fputs(
            $this->handle,
            "\n" .
            "msgid \"{$entry->getKey()}\"\n" .
            "msgstr \"$message\"\n"
        );
    }

    function close()
    {
        fclose($this->handle);
    }
}
