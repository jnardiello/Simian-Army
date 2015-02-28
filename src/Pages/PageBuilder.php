<?php

namespace Simian\Pages;

use GuzzleHttp\Stream\Stream;

class PageBuilder
{
    private $asin;

    public function setAsin($asin)
    {
        $this->asin = $asin;
        return $this;
    }

    public function setBody(Stream $htmlStream)
    {
        $this->body = $htmlStream;

        return $this;
    }

    public function build()
    {
        $page = new Page();
        $page->setProduct($this->asin)
             ->setHtml($this->body);

        return $page;
    }
}
