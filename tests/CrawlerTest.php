<?php

namespace Simian;

class CrawlerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->asins = [
            'B008EOJIVG',
        ];
        // http://amazon.it/gp/product/
        $this->fakeAmazonUrl = __DIR__ . "/fixtures/"; 
        $this->storageRoom = __DIR__ . "/storage/";
    }

    public function testCrawlerIsSavingLocallyWebPages()
    {
        $crawler = new Crawler($this->fakeAmazonUrl);
        $crawler->setStorageFolder($this->storageRoom)
                ->run($this->asins);

        $expectedFilename = $this->asins[0] . "-" . time() . ".html"; //ASIN-123456.html
        $this->assertTrue(file_exists($this->storageRoom . $expectedFilename));
    }
}
