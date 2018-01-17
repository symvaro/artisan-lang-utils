<?php

namespace Symvaro\ArtisanLangUtils\Writers;

use Symvaro\ArtisanLangUtils\Entry;

class ResourceWriter extends Writer
{
    private $entries;
    private $jsonEntries;

    private $uri;

    private $languageIdentifier;

    public function open($uri)
    {
        $this->uri = $uri;

        $this->languageIdentifier = array_last(explode('/', $uri));

        $this->entries = [];
        $this->jsonEntries = [];
    }

    public function write(Entry $entry)
    {
        $this->entries[$entry->getKey()] = $entry->getMessage();
    }

    public function close()
    {
        $this->sortEntries();

        dump($this->entries);
        $currentFile = null;
        $currentFileEntries = [];

        foreach ($this->entries as $key => $message) {
            if ($this->isJsonEntry($key)) {
                $this->jsonEntries[$key] = $message;
                continue;
            }

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

        $this->writeJsonEntries();
    }

    private function sortEntries()
    {
        ksort($this->entries);
    }

    private function isJsonEntry($key)
    {
        return strpos($key, '.') === false;
    }

    private function extractFilename($key)
    {
        $separatorPos = strpos($key, '.');

        if ($separatorPos === false) {
            // this comes into the json file
            return '../' . $this->languageIdentifier . '.json';
        }


        return substr($key, 0, strpos($key, '.'));
    }

    private function extractLangKey($key)
    {
        $separatorPos = strpos($key, '.');

        if ($separatorPos === false) {
            return $key;
        }

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

    private function writeJsonEntries()
    {
        file_put_contents($this->uri . '.json', json_encode($this->jsonEntries, JSON_PRETTY_PRINT));
    }
}