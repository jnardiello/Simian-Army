<?php

namespace Simian;

use Simian\Environment\Environment;

class CatalogueScraperTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->environment = new Environment('test');
        $this->merchantId = "A1010PM0QYBVOG";
        $client = new \MongoClient();
        $db = $client->selectDb($this->environment->get('mongo.data.db'));
        $this->collection = $db->selectCollection($this->environment->get('mongo.collection.merchants'));
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

    public function getStubbedScraper()
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

        $client->method('createRequest')
            ->willReturn($request);
        $client->method('send')
            ->willReturn($response);
        $response->method('getBody')
            ->willReturn($stubbedHtml);

        $marketplace = new Marketplace('uk');
        $scraper = new CatalogueScraper(
            $this->environment,
            $client
        );

        return $scraper;
    }

    public function test_scraper_will_retrieve_catalogue_products_from_seller_id()
    {
        $scraper = $this->getStubbedScraper();
        $scraper->run($this->merchantId);

        $expectedProducts = [
            [
                'asin' => 'asin-1',
                'active' => true,
            ],
            [
                'asin' => 'asin-2',
                'active' => true,
            ],
            [
                'asin' => 'asin-3',
                'active' => true,
            ],
        ];
        $catalogue = $this->collection->findOne([
            '_id' => $this->merchantId,
        ]);

        $this->assertEquals($expectedProducts, $catalogue['products']);
    }

    public function test_scraper_should_be_idempotent_when_adding_products()
    {
        $scraper = $this->getStubbedScraper();
        $scraper->run($this->merchantId);
        $scraper->run($this->merchantId);

        $expectedProducts = [
            [
                'asin' => 'asin-1',
                'active' => true,
            ],
            [
                'asin' => 'asin-2',
                'active' => true,
            ],
            [
                'asin' => 'asin-3',
                'active' => true,
            ],
        ];
        $catalogue = $this->collection->findOne([
            '_id' => $this->merchantId,
        ]);

        $this->assertEquals($expectedProducts, $catalogue['products']);
    }
}
