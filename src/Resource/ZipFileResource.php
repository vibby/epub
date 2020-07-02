<?php

/*
 * This file is part of the ePub Reader package
 *
 * (c) Justin Rainbow <justin.rainbow@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vibby\EPub\Resource;

use ZipArchive;

class ZipFileResource
{
    private $zipFile;
    private $cwd;
    private $zipFileName;

    public function __construct($file)
    {
        $this->zipFileName = $file;

        $this->zipFile = new ZipArchive();
        $this->zipFile->open($file);
    }

    public function setDirectory($dir)
    {
        $this->cwd = $dir;
    }

    public function get($name)
    {
        return $this->zipFile->getFromName($this->buildPath($name));
    }

    public function replaceNameFromString($name, $content): void
    {
        $name = $this->buildPath($name);
        $this->zipFile->deleteName($name);
        $this->zipFile->addFromString($name, $content);
        $this->zipFile->close();
    }

    private function buildPath($name)
    {
        if (null !== $this->cwd) {
            $name = $this->cwd . '/' . $name;
        }

        return $name;
    }

    public function getXML($name)
    {
        return simplexml_load_string($this->get($name));
    }

    public function all()
    {
        $result = array();

        for ($i = 0; $i < $this->zipFile->numFiles; $i++) {
            $item = $this->zipFile->statIndex($i);

            $result[] = $item['name'];
        }

        return $result;
    }

    public function getZipFileName()
    {
        return $this->zipFileName;
    }
}
