<?php

namespace Vibby\EPub\Definition;

class Navigation
{
    public $src;
    
    /**
     * Array of Chapters
     *
     * @var Chapter[]
     */
    public $chapters;
    
    
    public function __construct()
    {
        $this->src = new ManifestItem();
        $this->chapters = array();
    }
}
