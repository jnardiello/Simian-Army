<?php

namespace Simian\Repositories;

use Simian\Pages\Page;

class StorageRepository
{
    private $path;

    public function __construct($storagePath)
    {
        $this->path = $storagePath;
    }

    public function add(Page $page)
    {
        $htmlStream = $page->getBody();
        $asin = $page->getAsin();
        $filename = $this->getFilename($asin);

        file_put_contents($this->path . $filename, (string) $htmlStream);

        return $filename;
    }

    private function getFilename($asin)
    {
        return $asin . "-" . time() . ".html";
    }
}
