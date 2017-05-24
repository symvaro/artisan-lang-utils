<?php

namespace Symvaro\ArtisanLangUtils\Readers;

use Iterator;
use Symvaro\ArtisanLangUtils\Entry;
use Symvaro\ArtisanLangUtils\StringCollection;

abstract class Reader implements Iterator
{
    private $current;

    public function close() {}

    public function readAll() {
        return collect(iterator_to_array($this));
    }


    /**
     * @return \Symvaro\ArtisanLangUtils\Entry | null
     */
    protected abstract function nextEntry();

    /**
     * Resets the reader, so that nextEntry will read the first entry again.
     * @return void
     */
    protected abstract function reset();

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind() {
        $this->reset();

        $this->next();
    }


    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        if (!$this->valid()) {
            return null;
        }

        return $this->current->getMessage();
    }

    public function currentEntry()
    {
        if (!$this->valid()) {
            return null;
        }

        return $this->current;
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $this->current = $this->nextEntry();
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        if ($this->valid()) {
            return $this->current->getKey();
        }

        return null;
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return $this->current !== null;
    }

}
