<?php

namespace Symvaro\ArtisanLangUtils\Writers;

interface WriterContract
{
    function open($uri);

    function write($key, $message = "");
    
    function close();
}