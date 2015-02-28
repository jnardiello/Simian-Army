<?php

namespace Simian;

class CrawlerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->asins = [
            'B00G9ZVQ12',
            'B004ZXYU8Q',
        ];
        $this->baseUrl = "http://www.amazon.it/gp/product/";
        $this->storageRoom = __DIR__ . "/storage/";
    }

    public function tearDown()
    {
        exec("rm -Rf " . $this->storageRoom . "*");
    }

    public function testCrawlerIsSavingLocallyWebPages()
    {
        $expectedFilename = $this->asins[0] . "-" . time() . ".html"; //ASIN-123456.html

        $crawler = new Crawler($this->baseUrl, $this->storageRoom);
        $files = $crawler->run($this->asins);

        $this->assertEquals(2, count($files));
    }
}
