<?php


namespace Symvaro\ArtisanLangUtils\Writers;


use Symvaro\ArtisanLangUtils\Entry;

class TSVWriter extends Writer
{
    private $handle;
    
    public function open($uri)
    {
        $this->handle = fopen($uri, 'w');
    }

    public function write(Entry $entry)
    {
        $row = $this->escape($entry->key)
            . "\t"
            . $this->escape($entry->message)
            . "\n";
        fwrite($this->handle, $row);
    }

    public function close()
    {
        fclose($this->handle);
    }

    private function escape($string)
    {
        return str_replace(
            ["\\", "\t", "\r\n", "\n"],
            ["\\\\", "\\t", "\\r\\n", "\\n"],
            $string
        );
    }
}