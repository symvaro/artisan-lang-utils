<?php

namespace Symvaro\ArtisanLangUtils\Readers;

interface Reader
{
    public function open($uri);

    public function close();

    /**
     * @return \Symvaro\ArtisanLangUtils\Entry | null
     */
    public function next();
}
