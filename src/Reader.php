<?php

namespace Vibby\EPub;

use Vibby\EPub\Loader\ZipFileLoader;

class Reader
{
    private $loader;

    public function __construct()
    {
        $this->loader = new ZipFileLoader();
    }

    public function load($file)
    {
        return $this->loader->load($file);
    }
}