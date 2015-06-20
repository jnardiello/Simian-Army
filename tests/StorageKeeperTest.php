<?php

namespace Simian;

use Simian\Environment\Environment;
use GuzzleHttp\Client;
use GuzzleHttp\Message\Request;

class StorageKeeperTest extends \PHPUnit_Framework_TestCase
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
        $this->queueDb = $this->client->selectDB($this->environment->get('mongo.queues.db'));
        $this->productPagesQueue = $this->queueDb
                                        ->selectCollection("product_pages_queue");
    }

    public function tearDown()
    {
        exec("rm -Rf " . $this->storageRoom . "*");
        $this->productPagesQueue->remove();
    }

    public function testStorageKeeperIsSavingLocallyWebPages()
    {
        // Mocking guzzle
        $client = $this->getMockBuilder('GuzzleHttp\Client')
                       ->disableOriginalConstructor()
                       ->getMock();
        $request = $this->getMockBuilder('GuzzleHttp\Message\Request')
                        ->disableOriginalConstructor()
                        ->getMock();
        $response = $this->getMockBuilder('GuzzleHttp\Message\Response')
                         ->disableOriginalConstructor()
                         ->getMock();
        $htmlStream = $this->getMockBuilder('GuzzleHttp\Stream\Stream')
                           ->disableOriginalConstructor()
                           ->getMock();

        $client->method('createRequest')
               ->willReturn($request);
        $client->method('send')
               ->willReturn($response);
        $response->method('getBody')
                 ->willReturn($htmlStream);

        $storageKeeper = new StorageKeeper(
            $this->baseUrl, 
            $this->environment, 
            $client
        );
        $storageKeeper->sortByMostRecent()
                               ->run($this->asins);

//        $this->assertEquals(2, count($files));
        $this->assertEquals(2, $this->productPagesQueue->count());
    }
}
