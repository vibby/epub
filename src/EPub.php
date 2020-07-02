<?php

/*
 * This file is part of the ePub Reader package
 *
 * (c) Justin Rainbow <justin.rainbow@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vibby\EPub;

use Vibby\EPub\Definition\Package;
use Vibby\EPub\Definition\SpineItem;
use Vibby\EPub\Resource\ZipFileResource;
use Vibby\EPub\Resource\OpfResource;
use Vibby\EPub\Resource\NcxResource;

class EPub
{
    /** @var ZipFileResource */
    private $zip;
    private $package;

    /**
     * Reads in a ePub file and builds the Package definition
     *
     * @param string $file
     *
     * @return Package
     */
    public function loadFile($file): void
    {
        $zip = new ZipFileResource($file);

        $container = $zip->getXML('META-INF/container.xml');

        if (!$opfFile = (string) $container->rootfiles->rootfile['full-path']) {
            $ns = $container->getNamespaces();
            foreach ($ns as $key => $value) {
                $container->registerXPathNamespace($key, $value);
                $items = $container->xpath('//'. $key .':rootfile/@full-path');
                $opfFile = (string) $items[0]['full-path'];
            }
        }

        $data = $zip->get($opfFile);

        // all files referenced in the OPF are relative to it's directory
        if ('.' !== $dir = dirname($opfFile)) {
            $zip->setDirectory($dir);
        }

        $opfResource = new OpfResource($data, $zip);
        $package = $opfResource->bind();

        $package->opfDirectory = dirname($opfFile);

        if (isset($package->navigation->src->href)) {
            $ncx = $zip->get($package->navigation->src->href);
            $ncxResource = new NcxResource($ncx);
            $package = $ncxResource->bind($package);
        }

        $this->zip = $zip;
        $this->package = $package;
    }

    public function getPackage(): Package
    {
        return $this->package;
    }

    public function updateChapterContent(SpineItem $item, string $content): void
    {
        $item->setContent($content);
        $this->zip->replaceNameFromString($item->href, $content);
    }

    public function getZipFileName()
    {
        return $this->zip->getZipFileName();
    }
}
