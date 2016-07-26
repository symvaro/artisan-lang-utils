<?php


namespace Symvaro\ArtisanLangUtils;


class Entry
{
    private $key, $message;

    public function __construct($key, $message)
    {
        $this->key = $key;
        $this->message = $message;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getMessage()
    {
        return $this->message;
    }

}