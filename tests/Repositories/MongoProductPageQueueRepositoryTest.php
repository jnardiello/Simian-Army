<?php

namespace Simian\Repositories;

use Simian\Pages\PageBuilder;
use GuzzleHttp\Stream\Stream;
use Simian\Environment\Environment;

class MongoProductPageQueueRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->environment = new Environment('test');
        $this->client = new \MongoClient($this->environment->get('mongo.host'));
        $this->queueDb = $this->client->selectDB($this->environment->get('mongo.queues.db'));
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
        $pageRepository = new MongoProductPageQueueRepository(
            $this->environment->get('mongo.host'),
            $this->environment->get('mongo.queues.db'),
            "product_pages_queue"
        );
        $pageRepository->push($productPage, 'this-is-a-test-filename.html');

        $this->assertEquals(1, $this->productPagesQueue->count());
    }
}
