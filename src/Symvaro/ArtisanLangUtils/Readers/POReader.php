<?php

namespace Symvaro\ArtisanLangUtils\Readers;

use Symvaro\ArtisanLangUtils\Entry;

class POReader implements Reader
{
    private $handle;

    private $line;
    private $col;
    private $char;

    private $entryKey, $entryValue;

    public function open($uri)
    {
        $this->handle = fopen($uri, 'r');

        $this->readLine();
    }

    public function close()
    {
        fclose($this->handle);

        $this->handle = null;
    }

    private function readLine()
    {
        $this->line = fgets($this->handle);
        $this->col = 0;
        $this->nextChar();
    }

    private function isEof()
    {
        return $this->line === false;
    }

    /**
     * @return \Symvaro\ArtisanLangUtils\Entry | null
     */
    public function next()
    {
        $this->entryKey = null;
        $this->entryValue = null;

        if ($this->isEof()) {
            return null;
        }

        $this->parseEntry();

        if ($this->entryKey === null) {
            return null;
        }

        return new Entry($this->entryKey, $this->entryValue);
    }

    private function parseEntry()
    {
        $this->emptyLine();

        if ($this->isEof()) {
            return;
        }

        while ($this->comment() !== null);

        $this->keyword('msgid');
        $this->entryKey = $this->string();

        $this->keyword('msgstr');
        $this->entryValue = $this->string();
    }

    private function emptyLine()
    {
        if (empty(rtrim($this->line))) {
            $this->readLine();
        }
    }

    private function comment()
    {
        while ($this->char === ' ') {
            $this->nextChar();
        }

        if ($this->char !== '#') {
            return null;
        }

        $comment = mb_substr($this->line, $this->col - 1);

        $this->readLine();

        return $comment;
    }

    private function keyword($key)
    {
        for ($i = 0; $i < strlen($key); $i+=1) {
            if ($key[$i] !== $this->char) {
                throw new \ParseError();
            }

            $this->nextChar();
        }
    }


    private function string()
    {
        $string = $this->stringLine();

        if ($string === null) {
            return null;
        }

        while (true) {
            $this->readLine();

            $line = $this->stringLine();

            if ($line === null) {
                break;
            }

            $string .= $line;
        }

        return $string;
    }

    private function stringLine()
    {
        $string = '';

        if ($this->char == ' ') {
            while ($this->nextChar() === ' ');
        }

        if ($this->char !== '"') {
            return null;
        }

        while ($this->nextChar() !== '"') {
            $string .= $this->char;
        }

        return $string;
    }

    private function nextChar()
    {
        if ($this->line === false) {
            return false;
        }

        if ($this->col >= mb_strlen($this->line)) {
            $this->readLine();
        }

        if ($this->line === false) {
            return false;
        }

        $this->char = mb_substr($this->line, $this->col, 1);

        $this->col += 1;

        return $this->char;
    }

}