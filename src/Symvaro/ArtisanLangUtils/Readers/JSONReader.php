<?php


namespace Symvaro\ArtisanLangUtils\Readers;


use Symvaro\ArtisanLangUtils\Entry;

class JSONReader extends Reader
{
    private $values;

    private $iterator;

    public function __construct($uri)
    {
        $this->values = json_decode(file_get_contents($uri), true);

        $this->iterator = (new \ArrayObject($this->values))->getIterator();
    }

    /**
     * @return \Symvaro\ArtisanLangUtils\Entry | null
     */
    protected function nextEntry()
    {
        if (!$this->iterator->valid()) {
            return null;
        }

        $entry = new Entry($this->iterator->key(), $this->iterator->current());

        $this->iterator->next();

        return $entry;
    }

    /**
     * Resets the reader, so that nextEntry will read the first entry again.
     * @return void
     */
    protected function reset()
    {
        $this->iterator->rewind();
    }
}