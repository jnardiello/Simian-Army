<?php

namespace Simian;

use Simian\Environment\Environment;
use Simian\Seller;
use Simian\Marketplace;

class CatalogueScraperTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->environment = new Environment('test');
        $this->marketplace = new Marketplace('uk', $this->environment);
        $this->seller = new Seller(
            [
                "uk" => "A1010PM0QYBVOG"
            ], 
            'MediaDevil', 
            'email@mediadevil.com', 
            []
        );
        $client = new \MongoClient();
        $db = $client->selectDb($this->environment->get('mongo.data.db'));
        $this->collection = $db->selectCollection($this->environment->get('mongo.collection.sellers'));
        $this->collection->insert([
            'seller_ids' => $this->seller->getIds(),
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

        $scraper = new CatalogueScraper(
            $this->environment,
            $client,
            $this->marketplace
        );

        return $scraper;
    }

    public function test_scraper_will_retrieve_catalogue_products_from_seller_id()
    {
        $scraper = $this->getStubbedScraper();
        $scraper->run(
            $this->seller->getIds()[$this->marketplace->getSlug()]
        );

        $expectedProducts = [
            [
                'asin' => 'asin-1',
                'active' => true,
                'marketplace' => 'uk',
            ],
            [
                'asin' => 'asin-2',
                'active' => true,
                'marketplace' => 'uk',
            ],
            [
                'asin' => 'asin-3',
                'active' => true,
                'marketplace' => 'uk',
            ],
        ];
        $catalogue = $this->collection->findOne([
            'seller_ids' => $this->seller->getIds(),
        ]);

        $this->assertEquals($expectedProducts, $catalogue['products']);
    }

    public function test_scraper_should_be_idempotent_when_adding_products()
    {
        $scraper = $this->getStubbedScraper();
        $scraper->run(
            $this->seller->getIds()[$this->marketplace->getSlug()]
        );
        $scraper->run(
            $this->seller->getIds()[$this->marketplace->getSlug()]
        );

        $expectedProducts = [
            [
                'asin' => 'asin-1',
                'active' => true,
                'marketplace' => 'uk',
            ],
            [
                'asin' => 'asin-2',
                'active' => true,
                'marketplace' => 'uk',
            ],
            [
                'asin' => 'asin-3',
                'active' => true,
                'marketplace' => 'uk',
            ],
        ];
        $catalogue = $this->collection->findOne([
            "seller_ids.{$this->marketplace->getSlug()}" => 
            $this->seller->getIds()[$this->marketplace->getSlug()]
,
        ]);

        $this->assertEquals($expectedProducts, $catalogue['products']);
    }
}
