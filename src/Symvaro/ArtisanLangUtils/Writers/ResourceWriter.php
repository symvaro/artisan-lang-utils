<?php

namespace Symvaro\ArtisanLangUtils\Writers;

use Illuminate\Support\Arr;
use Symvaro\ArtisanLangUtils\Entry;

class ResourceWriter extends Writer
{
    private $entries;
    private $jsonEntries;

    private $initialFiles;
    private $writtenFiles;

    private $uri;

    private $languageIdentifier;

    public function open($uri)
    {
        $this->uri = $uri;

        $this->languageIdentifier = Arr::last(explode('/', $uri));

        $this->initialFiles = $this->mapKeysToFiles($this->getAllFiles($this->uri));

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

        $fileGroups = $this->wrapFilenames($this->entries);
        
        $this->writeJsonEntries($fileGroups->json);

        foreach ($fileGroups->files as $filename => $entries) {
            $this->writeEntries($filename, $entries);
            $this->writtenFiles[] = $filename;
        }

        $this->removeUnusedFiles();
    }

    /**
     * Figure out filenames from keys and map them thereby, figure out new filenames or else assign it to the json file.
     *
     * @param $entries
     * @return object
     */
    private function wrapFilenames($entries)
    {
        $files = [];
        $json = [];
        foreach ($entries as $key => $message) {
            $fileKey = $this->findFileKey($key);

            if (!$fileKey) {
                $fileKey = $this->extractFileKey($key);

                // can't extract filename so we store it in the json file
                if (!$fileKey) {
                    $json[$key] = $message;
                    continue;
                }
            }

            $key = $this->extractLangKey($key, $fileKey);
            $filename = $this->initialFiles[$fileKey] ?? ("$fileKey.php");
            
            if (!isset($files[$filename])) {
                $files[$filename] = [];
            }
            
            $files[$filename][$key] = $message;
        }

        return (object)[
            'json' => $json,
            'files' => $files,
        ];
    }

   private function sortEntries()
    {
        ksort($this->entries);
    }

    /**
     * Tries possible keys and checks if a file exits for given key.
     *
     * @param $key
     * @return string|null
     */
    private function findFileKey($key)
    {
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
        $separatorPos = strpos($key, '.');

        if ($separatorPos === false || $separatorPos == strlen($key) - 1) {
            return null; // no file key available
        }

        return substr($key, 0, strpos($key, '.'));
    }

    private function extractLangKey($key, $fileKey = null)
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

    private function writeJsonEntries($entries)
    {
        file_put_contents($this->uri . '.json', json_encode($entries, JSON_PRETTY_PRINT));
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
                $length = strlen($f) - $begin;
                $filename = substr($f, $begin, $length);
                $fileKeyPart = substr($filename, 0, strlen($filename) - strlen('.php'));
                $fileKey = str_replace('/', '.', $fileKeyPart);
                
                return [$fileKey => $filename];
            });
    }

    private function removeUnusedFiles()
    {
        foreach ($this->initialFiles as $filename) {
            if (!in_array($filename, $this->writtenFiles)) {
                unlink("{$this->uri}/$filename");
            }
        }
    }
}