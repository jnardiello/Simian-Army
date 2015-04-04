<?php

namespace Simian\Repositories;

use Simian\Environment\Environment;
use Simian\Seller;
use Simian\Marketplace;

class MongoCatalogueRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->environment = new Environment('test');
        $client = new \MongoClient($this->environment->get('mongo.host'));
        $mainDb = $client->selectDB($this->environment->get('mongo.data.db'));
        $this->merchantsCollection = $mainDb->selectCollection($this->environment->get('mongo.collection.merchants'));
        $this->marketplaceUK = new Marketplace('uk', $this->environment);
        $this->marketplaceDE = new Marketplace('de', $this->environment);
    }

    public function tearDown()
    {
        $this->merchantsCollection->remove([]);
    }

    public function testRepositoryCanAddProductToMerchant()
    {
        $merchantFixture = [
            '_id' => 'a-merchant-id',
            'name' => 'a-merchant-name',
            'products' => [],
        ];
        $expectedMerchantFixtures = [
            'a-merchant-id' => [
                '_id' => 'a-merchant-id',
                'name' => 'a-merchant-name',
                'products' => [
                    [
                        'asin' => 'a-product-asin',
                        'active' => true,
                        'marketplace' => 'A1F83G8C2ARO7P',
                    ],
                ]
            ]
        ];
        $this->merchantsCollection->insert($merchantFixture);

        $repository = new MongoCatalogueRepository($this->environment, 'a-merchant-id', $this->marketplaceUK);
        $productId = 'a-product-asin';

        $repository->add($productId);

        $merchantData = $this->merchantsCollection->find([
            '_id' => 'a-merchant-id'
        ]);
        $this->assertEquals($expectedMerchantFixtures, iterator_to_array($merchantData));
    }

    public function testRepositoryIsIdempotentWhenAddingANewProduct()
    {
        $merchantFixture = [
            '_id' => 'a-merchant-id',
            'name' => 'a-merchant-name',
            'products' => [],
        ];
        $expectedMerchantFixtures = [
            'a-merchant-id' => [
                '_id' => 'a-merchant-id',
                'name' => 'a-merchant-name',
                'products' => [
                    [
                        'asin' => 'a-product-asin',
                        'active' => true,
                        'marketplace' => 'A1F83G8C2ARO7P',
                    ],
                ]
            ]
        ];
        $this->merchantsCollection->insert($merchantFixture);

        $repository = new MongoCatalogueRepository($this->environment, 'a-merchant-id', $this->marketplaceUK);
        $productId = 'a-product-asin';

        $repository->add($productId);
        $repository->add($productId);

        $merchantDataCursor = $this->merchantsCollection->find([
            '_id' => 'a-merchant-id'
        ]);

        $this->assertEquals(1, $merchantDataCursor->count());
        $this->assertEquals($expectedMerchantFixtures, iterator_to_array($merchantDataCursor));
    }

    public function testRepositoryCanRecoverListOfProducts()
    {
        $merchantFixture = [
            '_id' => 'a-merchant-id',
            'name' => 'a-merchant-name',
            'products' => [],
        ];
        $expectedProducts = ['a-product-asin', 'another-product-asin'];

        $this->merchantsCollection->insert($merchantFixture);

        $repository = new MongoCatalogueRepository($this->environment, 'a-merchant-id', $this->marketplaceUK);
        $repository->add('a-product-asin');
        $repository->add('another-product-asin');

        $products = $repository->getProductsCatalogue();
        $this->assertEquals($expectedProducts, $products);
    }

    public function test_repository_will_add_two_products_with_same_asin_but_different_marketplace()
    {
        $merchantFixture = [
            '_id' => 'a-merchant-id',
            'name' => 'a-merchant-name',
            'products' => [],
        ];
        $expectedMerchantFixtures = [
            'a-merchant-id' => [
                '_id' => 'a-merchant-id',
                'name' => 'a-merchant-name',
                'products' => [
                    [
                        'asin' => 'a-product-asin',
                        'active' => true,
                        'marketplace' => 'A1F83G8C2ARO7P',
                    ],
                    [
                        'asin' => 'a-product-asin',
                        'active' => true,
                        'marketplace' => 'A1PA6795UKMFR9',
                    ],
                ]
            ]
        ];
        $this->merchantsCollection->insert($merchantFixture);

        $repositoryUK = new MongoCatalogueRepository($this->environment, 'a-merchant-id', $this->marketplaceUK);
        $repositoryDE = new MongoCatalogueRepository($this->environment, 'a-merchant-id', $this->marketplaceDE);
        $productId = 'a-product-asin';

        $repositoryUK->add($productId);
        $repositoryDE->add($productId);

        $merchantDataCursor = $this->merchantsCollection->find([
            '_id' => 'a-merchant-id'
        ]);

        $this->assertEquals(1, $merchantDataCursor->count());
        $this->assertEquals($expectedMerchantFixtures, iterator_to_array($merchantDataCursor));
    }
}
