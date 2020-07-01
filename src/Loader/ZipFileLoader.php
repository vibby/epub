<?php

/*
 * This file is part of the ePub Reader package
 *
 * (c) Justin Rainbow <justin.rainbow@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vibby\EPub\Loader;

use Vibby\EPub\Definition\Package;
use Vibby\EPub\Resource\ZipFileResource;
use Vibby\EPub\Resource\OpfResource;
use Vibby\EPub\Resource\NcxResource;

class ZipFileLoader
{
    /**
     * Reads in a ePub file and builds the Package definition
     *
     * @param string $file
     *
     * @return Package
     */
    public function load($file): Package
    {
        $resource = new ZipFileResource($file);

        $package = $resource->getXML('META-INF/container.xml');

        if (!$opfFile = (string) $package->rootfiles->rootfile['full-path']) {
            $ns = $package->getNamespaces();
            foreach ($ns as $key => $value) {
                $package->registerXPathNamespace($key, $value);
                $items = $package->xpath('//'. $key .':rootfile/@full-path');
                $opfFile = (string) $items[0]['full-path'];
            }
        }

        $data = $resource->get($opfFile);

        // all files referenced in the OPF are relative to it's directory
        if ('.' !== $dir = dirname($opfFile)) {
            $resource->setDirectory($dir);
        }

        $opfResource = new OpfResource($data, $resource);
        $package = $opfResource->bind();
        
        $package->opfDirectory = dirname($opfFile);
        
        if ($package->navigation->src->href) {
            $ncx = $resource->get($package->navigation->src->href);
            $ncxResource = new NcxResource($ncx);
            $package = $ncxResource->bind($package);
        }
        
        return $package;
    }
}
