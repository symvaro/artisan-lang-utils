<?php


namespace Symvaro\ArtisanLangUtils;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use IteratorAggregate;

class StringCollection implements Arrayable, IteratorAggregate
{
    private $strings;

    /**
     * StringCollection constructor.
     * @param Collection|array $strings
     * @throws \Exception
     */
    public function __construct($strings)
    {
        $strings = is_array($strings) ? collect($strings) : $strings;

        if (!($strings instanceof Collection)) {
            throw new \Exception('invalid argument');
        }

        $this->strings = $strings;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->strings->toArray();
    }

    public function getIterator()
    {
        return $this->toArray();
    }
}