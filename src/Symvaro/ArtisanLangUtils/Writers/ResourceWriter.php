<?php

namespace Symvaro\ArtisanLangUtils\Writers;

use Illuminate\Support\Arr;
use Symvaro\ArtisanLangUtils\Entry;

class ResourceWriter extends Writer
{
    private $entries;

    private $uri;

    public function open($uri)
    {
        $this->uri = $uri;

        $this->entries = [];
    }

    public function write(Entry $entry)
    {
        $this->entries[$entry->getKey()] = $entry->getMessage();
    }

    public function close()
    {
        $this->sortEntries();

        $currentFile = null;
        $currentFileEntries = [];

        foreach ($this->entries as $key => $message) {
            $filename = $this->extractFilename($key);

            if ($currentFile === null) {
                $currentFile = $filename;
            } else if ($currentFile !== $filename) {
                $this->writeEntries($currentFile, $currentFileEntries);
                $currentFile = $filename;
                $currentFileEntries = [];
            }

            $currentFileEntries[$this->extractLangKey($key)] = $message;
        }

        $this->writeEntries($currentFile, $currentFileEntries);
    }

    private function sortEntries()
    {
        ksort($this->entries);
    }

    private function extractFilename($key)
    {
        return substr($key, 0, strpos($key, '.'));
    }

    private function extractLangKey($key)
    {
        return substr($key, strpos($key, '.') + 1);
    }

    private function writeEntries($filePath, array $entries)
    {
        $filePath = $this->uri . '/' . $filePath;

        echo $filePath . "\n";

        $pathElements = explode('/', $filePath);

        $file = $pathElements[sizeof($pathElements) - 1];

        $dir = substr($filePath, 0, strlen($filePath) - strlen($file));

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $f = fopen($filePath . '.php', 'w');

        fwrite($f, "<?php\n\nreturn [\n");

        foreach ($entries as $key => $value) {
            $value = str_replace('\'', '\\\'', $value);

            fwrite($f, "    '$key' => '$value',\n");
        }

        fwrite($f, "];\n");
    }
}