<?php


namespace Symvaro\ArtisanLangUtils\Writers;


use Symvaro\ArtisanLangUtils\Entry;

class JSONWriter extends Writer
{
    private $handle;
    private $prettyPrint = true;
    private $isFirst = true;

    public function open($uri)
    {
        $this->handle = fopen($uri, 'w');
        fputs($this->handle, '{');
        $this->isFirst = true;
    }

    public function write(Entry $entry)
    {
        $str = '';
        if ($this->isFirst) {
            $this->isFirst = false;
        }
        else {
            $str .= ',';
        }

        if ($this->prettyPrint) {
            $str .= "\n\t";
        }

        $str .= json_encode($entry->key) . ': ' . json_encode($entry->message);


        fputs($this->handle, $str);
    }

    public function close()
    {
        $close = '}';
        if ($this->prettyPrint) {
            $close = "\n$close";
        }
        fputs($this->handle, $close);

        fclose($this->handle);
        $this->handle = null;
    }


}