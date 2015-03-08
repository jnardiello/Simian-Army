<?php

namespace Simian;

use GuzzleHttp\Client;
use Simian\Environment\Environment;

class CatalogueScraperTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->environment = new Environment('test');
        $this->merchantId = "A1010PM0QYBVOG";
        $client = new \MongoClient();
        $db = $client->selectDb($this->environment->get('mongo.data.db'));
        $this->collection = $db->selectCollection($this->environment->get('mongo.merchants'));
        $this->collection->insert([
            '_id' => $this->merchantId,
            'name' => 'Mediadevil',
            'products' => [],
        ]);
    }

    public function tearDown()
    {
        $this->collection->remove([]);
    }

    public function testScraperWillRetrieveCatalogueProductsFromSellerId()
    {
        $stubbedHtml = "
            <div id='resultsCol'>
                <li data-asin='asin-1'></li>
                <li data-asin='asin-2'></li>
                <li data-asin='asin-3'></li>
            </div>
        ";
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
                 ->willReturn($stubbedHtml);

        $scraper = new CatalogueScraper(
            $this->environment,
            $client
        );

        $scraper->run($this->merchantId);

        $expectedProducts = [
            [
                'asin' => 'asin-1',
                'active' => false,
            ],
            [
                'asin' => 'asin-2',
                'active' => false,
            ],
            [
                'asin' => 'asin-3',
                'active' => false,
            ],
        ];
        $catalogue = $this->collection->findOne([
            '_id' => $this->merchantId,
        ]);

        $this->assertEquals($expectedProducts, $catalogue['products']);
    }
}
