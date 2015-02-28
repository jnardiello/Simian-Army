<?php

namespace Simian;

class Crawler
{
    private $storage;

    public function setStorageFolder($path)
    {
        $this->storage = $path;

        return $this;
    }

    public function run()
    {
        file_put_contents($this->storage . "B008EOJIVG-" . time() . ".html", "hello");
    }
}
