<?php

namespace Simian\Repositories;

use Simian\Pages\PageBuilder;
use GuzzleHttp\Stream\Stream;

class MongoProductPageQueueRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->client = new \MongoClient();
        $this->queueDb = $this->client->selectDB("test");
        $this->productPagesQueue = $this->queueDb
                                        ->selectCollection("product_pages_queue");
    }

    public function tearDown()
    {
        $this->productPagesQueue->remove();
    }

    public function testCanPushElementInTheQueue()
    {
        $htmlStream = Stream::factory("<h1>Hello World</h1>");

        $pageBuilder = new PageBuilder();
        $productPage = $pageBuilder->setAsin("B00KDRUCJY")
                                   ->setBody($htmlStream)
                                   ->build();
        $pageRepository = new MongoProductPageQueueRepository("test", "product_pages_queue");
        $pageRepository->push($productPage, 'this-is-a-test-filename.html');

        $this->assertEquals(1, $this->productPagesQueue->count());
    }
}
