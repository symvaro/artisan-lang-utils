<?php

namespace Symvaro\ArtisanLangUtils\Readers;

use Iterator;
use Symvaro\ArtisanLangUtils\Entry;

abstract class Reader implements Iterator
{
    private $current;

    public function close() {}

    public function allMessages() {
        return collect(iterator_to_array($this))
            ->map(function ($e) {
                return $e->message;
            });
    }

    public function allEntries() {
        return collect(iterator_to_array($this));
    }

    /**
     * @return Entry | null
     */
    protected abstract function nextEntry();

    /**
     * Resets the reader, so that nextEntry will read the first entry again.
     * @return void
     */
    protected abstract function reset();

    public function rewind() {
        $this->reset();

        $this->next();
    }


    public function current()
    {
        if (!$this->valid()) {
            return null;
        }

        return $this->current;
    }

    public function next()
    {
        $this->current = $this->nextEntry();
    }

    public function key()
    {
        return $this->current()->key ?? null;
    }

    public function valid()
    {
        return $this->current !== null;
    }

}
