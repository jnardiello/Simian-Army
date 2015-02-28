<?php

namespace Simian\Pages;

class Page
{
    private $asin;
    private $html;

    public function setProduct($asin)
    {
        $this->asin = $asin;
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
}
