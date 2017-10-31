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
            }
            else if ($currentFile !== $filename) {
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

    private $currentLevel;

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

        $this->currentLevel = [];

        foreach ($entries as $key => $value) {
            $level = explode('.', $key);

            for ($i = 0; $i < sizeof($level) - 1 && $i < sizeof($this->currentLevel); $i += 1) {
                if ($level[$i] != $this->currentLevel[$i]) {
                    break;
                }
            }

            if ($i < sizeof($this->currentLevel)) {
                $this->closeLevels($f, sizeof($this->currentLevel) - $i);
            }

            for (; $i < sizeof($level) - 1; $i += 1) {
                $this->intend($f, sizeof($this->currentLevel) + $i + 1);
                fwrite($f, "'{$level[$i]}' => [\n");
            }

            $this->currentLevel = $level;
            unset($this->currentLevel[sizeof($this->currentLevel) -1]);

            $value = str_replace('\'', '\\\'', $value);

            $this->intend($f, sizeof($this->currentLevel) + 1);
            fwrite($f, "'{$level[sizeof($level)-1]}' => '$value',\n");
        }

        $this->closeLevels($f, sizeof($this->currentLevel));

        fwrite($f, "];\n");
    }

    private function closeLevels($handle, $count)
    {
        for ($i = 0; $i < $count; $i += 1) {
            $this->intend($handle, sizeof($this->currentLevel) - $i);
            fwrite($handle, "],\n");
        }
    }

    private function intend($handle, $level)
    {
        $str = '';

        for ($i = 0; $i<$level; $i+=1) {
            $str .= '    ';
        }

        fwrite($handle, $str);
    }
}