<?php

namespace Symvaro\ArtisanLangUtils\Writers;

use Symvaro\ArtisanLangUtils\Entry;

class ResourceWriter extends Writer
{
    private $entries;
    private $jsonEntries;

    private $initialFiles;
    private $unneededFiles;

    private $uri;

    private $languageIdentifier;

    public function open($uri)
    {
        $this->uri = $uri;

        $this->languageIdentifier = array_last(explode('/', $uri));

        $this->initialFiles = $this->mapKeysToFiles($this->getAllFiles($this->uri));
        $this->unneededFiles = $this->initialFiles;

        $this->entries = [];
        $this->jsonEntries = [];
    }

    public function write(Entry $entry)
    {
        $this->entries[$entry->getKey()] = $entry->getMessage();
    }

    public function close()
    {
        /**
         * TODO Refactor writing algorithm:
         *
         * resourceFileExistsFor(key, dir = '.'):
         *   file = first_part(key);
         *
         *   is_dir(file):
         *      return existsInFile(key - file, file);
         *
         *   return is_file(file);
         *
         * if (resourceFileExistsFor(key)):
         *   write to resource;
         *   return;
         *
         * if (validFilename(first_part(key))):
         *   write to resource;
         *   return;
         *
         * write to json
         * return;
         *
         * Collect all files up front and delete ones that weren't used. (only if flag?)
         */

        $this->sortEntries();

        $currentFile = null;
        $currentFileEntries = [];

        foreach ($this->entries as $key => $message) {

            $sourceFileKey = $this->getFileKeyOfSource($key);

            if (!$sourceFileKey) {

            }
            if ($this->isJsonEntry($key)) {
                $this->jsonEntries[$key] = $message;
                continue;
            }

            $filename = $this->extractFilename($key);

            if ($currentFile === null) {
                // start new file
                $currentFile = $filename;
            } else if ($currentFile !== $filename) {
                // finish write to file
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
        if (strpos($key, '.') === false) {
            return true;
        }
    }

    private function getFileKeyOfSource($key) {

        $fileKeyParts = explode($key, '.');

        while (true) {
            array_pop($fileKeyParts);

            if (empty($fileKeyParts)) {
                break;
            }

            $fileKey = implode('.', $fileKeyParts);

            if (isset($this->initialFiles[$fileKey])) {
                return $fileKey;
            }
        }

        return null;
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
        if ($filePath === null) {
            return;
        }

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

    private function getAllFiles($uri)
    {
        $result = [];
        $files = glob($uri . '/*');

        foreach ($files as $file) {
            if (is_dir($file)) {
                $result = array_merge($result, $this->getAllFiles($file));
            }
            else {
                $result[] = $file;
            }
        }

        return $result;
    }

    private function mapKeysToFiles(array $filenames)
    {
        $begin = strlen($this->uri) + 1;

        return collect($filenames)
            ->mapWithKeys(function ($f) use ($begin) {
                $length = strlen($f) - $begin - strlen('.php');
                $fileName = substr($f, $begin, $length);
                return [str_replace('/', '.', $fileName) => $f];
            });
    }
}