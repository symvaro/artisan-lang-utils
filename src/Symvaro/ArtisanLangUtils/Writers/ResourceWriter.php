<?php

namespace Symvaro\ArtisanLangUtils\Writers;

use Illuminate\Support\Arr;
use Symvaro\ArtisanLangUtils\Entry;

class ResourceWriter extends Writer
{
    private $initialFiles;
    private $writtenFiles;

    private $uri;

    private $languageIdentifier;
    private $jsonOnly = false;
    
    private $jsonEntries, $files;

    public function open($uri)
    {
        $this->uri = $uri;
        $this->languageIdentifier = Arr::last(explode('/', $uri));
        $this->initialFiles = $this->mapKeysToFiles($this->getAllFiles($this->uri));
        $this->jsonEntries = [];
        $this->files = [];
    }

    /**
     * Extract keys from filenames and map them like:
     *
     * [ "subdir.subfile' => "subdir/subfile.php" ]
     *
     * @param array $filenames
     * @return \Illuminate\Support\Collection
     */
    private function mapKeysToFiles(array $filenames)
    {
        $begin = strlen($this->uri) + 1;

        return collect($filenames)
            ->mapWithKeys(function ($f) use ($begin) {
                $length = strlen($f) - $begin;
                $filename = substr($f, $begin, $length);
                $fileKeyPart = substr($filename, 0, strlen($filename) - strlen('.php'));
                $fileKey = str_replace('/', '.', $fileKeyPart);

                return [$fileKey => $filename];
            });
    }

    public function outputJsonOnly()
    {
        $this->jsonOnly = true;
        return $this;
    }

    public function write(Entry $entry)
    {
        if ($this->jsonOnly) {
            $this->addToJson($entry);
            return;
        }

        $fileKey = $this->findFileKey($entry->key);

        if (!$fileKey) {
            $fileKey = $this->extractFileKey($entry->key);

            // can't extract filename so we store it in the json file
            if (!$fileKey) {
                $this->addToJson($entry);
                return;
            }
        }

        $this->addToFile($fileKey, $entry);
    }

    private function addToJson(Entry $entry)
    {
        $this->jsonEntries[$entry->key] = $entry->message;
    }

    private function addToFile($fileKey, Entry $entry)
    {
        $key = $this->extractLangKey($entry->key, $fileKey);
        $filename = $this->initialFiles[$fileKey] ?? ("$fileKey.php");

        if (!isset($this->files[$filename])) {
            $this->files[$filename] = [];
        }

        $this->files[$filename][$key] = $entry->message;
    }

    public function close()
    {
        $this->writeJsonEntries();

        foreach ($this->files as $filename => $entries) {
            $this->writeEntries($filename, $entries);
            $this->writtenFiles[] = $filename;
        }

        $this->removeUnusedFiles();
    }


    /**
     * Tries possible keys and checks if a file exits for given key.
     *
     * @param $key
     * @return string|null
     */
    private function findFileKey($key)
    {
        $key = str_replace('/', '.', $key);
        $fileKeyParts = explode('.', $key);

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
    private function extractFileKey($key)
    {
        $key = str_replace('/', '.', $key);
        $separatorPos = strpos($key, '.');

        if ($separatorPos === false || $separatorPos == strlen($key) - 1) {
            return null; // no file key available
        }

        return substr($key, 0, strpos($key, '.'));
    }

    private function extractLangKey($key, $fileKey)
    {
        if ($fileKey) {
            return substr($key, strlen($fileKey) + 1);
        }

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

        $pathElements = explode('/', $filePath);

        $file = $pathElements[sizeof($pathElements) - 1];

        $dir = substr($filePath, 0, strlen($filePath) - strlen($file));

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $f = fopen($filePath, 'w');

        fwrite($f, "<?php\n\nreturn [\n");

        foreach ($entries as $key => $value) {
            $value = str_replace('\'', '\\\'', $value);

            fwrite($f, "    '$key' => '$value',\n");
        }

        fwrite($f, "];\n");
    }

    private function writeJsonEntries()
    {
        $file = $this->uri . '.json';
        if (!empty($this->jsonEntries)) {
            file_put_contents($file, json_encode($this->jsonEntries, JSON_PRETTY_PRINT));
            return;
        }
        
        if (file_exists($file)) {
            unlink($file);
        }
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

    private function removeUnusedFiles()
    {
        foreach ($this->initialFiles as $filename) {
            if (!in_array($filename, $this->writtenFiles ?? [])) {
                unlink("{$this->uri}/$filename");
            }
        }
    }
}