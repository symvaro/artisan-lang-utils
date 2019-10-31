<?php


namespace Symvaro\ArtisanLangUtils;


class Entry
{
    public $key, $message;

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

    public function __toString()
    {
        return $this->message;
    }
}