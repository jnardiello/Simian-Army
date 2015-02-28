<?php

namespace Simian;

class CrawlerTest extends \PHPUnit_Framework_TestCase
{
    public function testCrawlerIsSavingLocallyWebPages()
    {
        $asins = [
            'B008EOJIVG',
        ];
        $storageRoom = __DIR__ . "/storage/";
        $fakeAmazonUrl = __DIR__ . "/fixtures/"; // should be http://amazon.it/gp/product/

        $crawler = new Crawler($fakeAmazonUrl);
        $crawler->setStorageFolder($storageRoom)
                ->run($asins);

        $expectedFilename = "B008EOJIVG-" . time() . ".html";
        $this->assertTrue(file_exists($storageRoom . $expectedFilename));
    }
}
