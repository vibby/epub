<?php

require_once __DIR__.'/../bootstrap.php';

use Vibby\EPub\Reader;
use Vibby\EPub\Loader\ZipFileLoader;

$reader = new Reader();
$epub   = $reader->load($argv[1]);

printf("Title: %s\n", $epub->getMetadata()->get('title'));

