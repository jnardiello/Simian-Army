<?php

namespace Simian;

use Simian\Environment\Environment;

class CrawlerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->environment = new Environment('test');
        $this->storageRoom = $this->environment->get('storage.path');
        $this->asins = [
            'B00G9ZVQ12',
            'B004ZXYU8Q',
        ];
        $this->baseUrl = "http://www.amazon.it/product-reviews/";
        $this->client = new \MongoClient($this->environment->get('mongo.host'));
        $this->queueDb = $this->client->selectDB("test");
        $this->productPagesQueue = $this->queueDb
                                        ->selectCollection("product_pages_queue");
    }

    public function tearDown()
    {
        exec("rm -Rf " . $this->storageRoom . "*");
        $this->productPagesQueue->remove();
    }

    public function testCrawlerIsSavingLocallyWebPages()
    {
        $crawler = new Crawler($this->baseUrl, $this->environment);
        $files = $crawler
            ->sortByMostRecent()
            ->run($this->asins);

        $this->assertEquals(2, count($files));
        $this->assertEquals(2, $this->productPagesQueue->count());
    }
}
