<?php

namespace Symvaro\ArtisanLangUtils;


class StringUtils
{
    public static function startsWith($string, $start)
    {
        return substr($string, 0, strlen($start)) === $start;
    }
}