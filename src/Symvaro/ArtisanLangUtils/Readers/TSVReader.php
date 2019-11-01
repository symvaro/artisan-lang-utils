<?php


namespace Symvaro\ArtisanLangUtils\Readers;

use Symvaro\ArtisanLangUtils\Entry;

class TSVReader extends Reader
{
    private $handle;
    private $rowNumber;

    public function open($uri)
    {
        $this->handle = fopen($uri, 'r');
        $this->rowNumber = 0;
    }

    /**
     * @return Entry | null
     */
    protected function nextEntry()
    {
        $this->rowNumber += 1;
        $row = fgets($this->handle);
        
        if (!$row) {
            return null;
        }

        // remove line ending from input
        $row = substr($row, 0, strlen($row) - 1);

        list ($key, $message) = explode("\t", $row);

        return new Entry(
            $this->unescape($key),
            $this->unescape($message)
        );
        
    }

    private function unescape($string)
    {
        return str_replace(
            ["\\\\", "\\t", "\\r\\n", "\\n"],
            ["\\", "\t", "\r\n", "\n"],
            $string
        );
    }

    /**
     * Resets the reader, so that nextEntry will read the first entry again.
     * @return void
     */
    protected function reset()
    {
        if ($this->rowNumber > 0) {
            rewind($this->handle);
        }
    }

    public function close()
    {
        fclose($this->handle);
    }
}