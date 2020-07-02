<?php

namespace Vibby\EPub\Reader;

use Vibby\EPub\EPub;

class Reader
{
    public function load($file): EPub
    {
        $ePub = new EPub();
        $ePub->loadFile($file);

        return $ePub;
    }
}
