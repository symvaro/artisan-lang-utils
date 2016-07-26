<?php

namespace Symvaro\ArtisanLangUtils\Writers;

class POWriter extends Writer
{
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
}
