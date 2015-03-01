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

        $this->client = new \MongoClient();
        $this->queueDb = $this->client->selectDB("test");
        $this->productPagesQueue = $this->queueDb
                                        ->selectCollection("product_pages_queue");
    }

    public function tearDown()
    {
        exec("rm -Rf " . $this->storageRoom . "*");
    }

    public function testCrawlerIsSavingLocallyWebPages()
    {
        $crawler = new Crawler($this->baseUrl, $this->storageRoom);
        $files = $crawler->run($this->asins);

        $this->assertEquals(2, count($files));
        $this->assertEquals(2, $this->productPagesQueue->count());
    }
}
