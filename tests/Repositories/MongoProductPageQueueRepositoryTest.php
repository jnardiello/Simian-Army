<?php

namespace Simian\Repositories;

use Simian\Pages\PageBuilder;
use GuzzleHttp\Stream\Stream;
use Simian\Environment\Environment;

class MongoProductPageQueueRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $environment = new Environment('test');
        $client = new \MongoClient($environment->get('mongo.host'));
        $queueDb = $client->selectDB($environment->get('mongo.queues.db'));
        $this->productPagesQueue = $queueDb->selectCollection("product_pages_queue");

        $htmlStream = Stream::factory("<h1>Hello World</h1>");

        $pageBuilder = new PageBuilder();
        $this->productPage = $pageBuilder->setAsin("B00KDRUCJY")
                                         ->setBody($htmlStream)
                                         ->build();
        $this->pageRepository = new MongoProductPageQueueRepository(
                                        $environment->get('mongo.host'),
                                        $environment->get('mongo.queues.db'),
                                        "product_pages_queue"
                                    );
    }

    public function tearDown()
    {
        $this->productPagesQueue->remove();
    }

    public function testCanPushElementInTheQueue()
    {
        $this->pageRepository->push(
            $this->productPage, 
            'this-is-a-test-filename.html'
        );

        $this->assertEquals(1, $this->productPagesQueue->count());
    }

    public function testWillConsumePagesInQueue()
    {
        $this->pageRepository->push(
            $this->productPage, 
            'this-is-a-test-filename.html'
        );
        $documents = $this->pageRepository->consume();

        $this->assertEquals(1, count($documents));
        $this->assertTrue(array_key_exists('asin', $documents[0]));
        $this->assertEquals(0, $this->productPagesQueue->count());
    }

    public function testWillConsumeUpTo10PagesAtATime()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->pageRepository->push(
                $this->productPage, 
                'this-is-a-test-filename.html'
            );
        }

        $documents = $this->pageRepository->consume();

        $this->assertEquals(10, count($documents));
        $this->assertEquals(0, $this->productPagesQueue->count());
    }
}
