<?php

namespace Simian\Pages;

use GuzzleHttp\Stream\Stream;

class PageBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBuilderCanBuildPage()
    {
        $asin = "B0058DXA4W";

        $htmlStream = Stream::factory('test page');

        $expectedPage = new Page();
        $expectedPage->setProduct($asin)
                     ->setHtml($htmlStream);

        $pageBuilder = new PageBuilder();
        $page = $pageBuilder->setAsin($asin)
                            ->setBody($htmlStream)
                            ->build();

        $this->assertEquals($expectedPage, $page);
    }
}
