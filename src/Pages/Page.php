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
}
