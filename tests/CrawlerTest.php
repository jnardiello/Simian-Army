<?php

namespace Simian;

class CrawlerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->asins = [
            'B0058DXA4W',
        ];
        $this->baseUrl = "http://www.amazon.it/gp/product/";
        $this->storageRoom = __DIR__ . "/storage/";
    }

    public function testCrawlerIsSavingLocallyWebPages()
    {
        $expectedFilename = $this->asins[0] . "-" . time() . ".html"; //ASIN-123456.html

        $crawler = new Crawler($this->baseUrl);
        $crawler->setStorageFolder($this->storageRoom)
                ->run($this->asins);

        $this->assertTrue(file_exists($this->storageRoom . $expectedFilename));
    }
}
