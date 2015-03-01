<?php

namespace Simian\Pages;

class Page
{
    private $asin;
    private $html;
    private $time;

    public function setProduct($asin)
    {
        $this->asin = $asin;
        $this->time = time();
        return $this;
    }

    public function setHtml($htmlStream)
    {
        $this->html = $htmlStream;
        return $this;
    }

    public function getBody()
    {
        return $this->html;
    }

    public function getAsin()
    {
        return $this->asin;
    }

    public function getTime()
    {
        return $this->time;
    }
}
