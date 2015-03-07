<?php

namespace Simian;

use GuzzleHttp\Client;
use Simian\Environment\Environment;

class CatalogueScraperTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->merchantId = "A1010PM0QYBVOG";
    }

    public function testScraperWillRetrieveCatalogueProductsFromSellerId()
    {
        $environment = new Environment('test');
        $scraper = new CatalogueScraper(
            $environment,
            new Client(),
            new CatalogueRepository()
        );

        $scraper->run($this->merchantId);

        $expectedProducts = [
            'B00G9ZVQ12',
            'B004ZXYU8Q',
        ];

        $this->assertEquals($expectedProducts, $products);
    }
}
