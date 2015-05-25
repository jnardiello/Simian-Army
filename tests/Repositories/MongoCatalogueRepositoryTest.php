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
        $this->merchantsCollection = $mainDb->selectCollection($this->environment->get('mongo.collection.sellers'));
        $this->marketplaceUK = new Marketplace('uk', $this->environment);
        $this->marketplaceDE = new Marketplace('de', $this->environment);
        $this->marketplaceIT = new Marketplace('it', $this->environment);
    }

    public function tearDown()
    {
        $this->merchantsCollection->remove([]);
    }

    public function test_repository_can_add_product_to_merchant_with_multiple_ids()
    {
        $mongoId = new \MongoId();
        $merchantFixture = [
            '_id' => $mongoId,
            'seller_ids' => [
                $this->marketplaceUK->getSlug() => 'a-merchant-id',
                $this->marketplaceDE->getSlug() => 'another-merchant-id',
            ],
            'name' => 'a-merchant-name',
            'products' => [],
        ];
        $expectedMerchantFixtures = [
            $mongoId->__toString() => [
                '_id' => $mongoId,
                'seller_ids' => [
                    'uk' => 'a-merchant-id',
                    'de' => 'another-merchant-id',
                ],
                'name' => 'a-merchant-name',
                'products' => [
                    [
                        'asin' => 'a-product-asin',
                        'active' => true,
                        'marketplace' => 'uk',
                    ],
                ]
            ]
        ];
        $this->merchantsCollection->insert($merchantFixture);

        $repository = new MongoCatalogueRepository($this->environment, 'a-merchant-id', $this->marketplaceUK);

        $productId = 'a-product-asin';
        $repository->add($productId);

        $merchantData = $this->merchantsCollection->find([
            '_id' => $mongoId,
        ]);
        $this->assertEquals($expectedMerchantFixtures, iterator_to_array($merchantData));
    }

    public function test_repository_is_idempotent_when_adding_new_product()
    {
        $mongoId = new \MongoId();
        $merchantFixture = [
            '_id' => $mongoId,
            'seller_ids' => [
                'uk' => 'a-merchant-id'
            ],
            'name' => 'a-merchant-name',
            'products' => [],
        ];
        $expectedMerchantFixtures = [
            $mongoId->__toString() => [
                '_id' => $mongoId,
                'seller_ids' => [
                    'uk' => 'a-merchant-id'
                ],
                'name' => 'a-merchant-name',
                'products' => [
                    [
                        'asin' => 'a-product-asin',
                        'active' => true,
                        'marketplace' => 'uk',
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
            "seller_ids.{$this->marketplaceUK->getSlug()}" => 'a-merchant-id'
        ]);

        $this->assertEquals(1, $merchantDataCursor->count());
        $this->assertEquals($expectedMerchantFixtures, iterator_to_array($merchantDataCursor));
    }

    public function test_repository_can_recover_list_of_products_per_marketplace()
    {
        $mongoId = new \MongoId();
        $merchantFixture = [
            '_id' => $mongoId,
            'seller_ids' => [
                'it' => 'a-merchant-id',
                'de' => 'a-merchant-id',
            ],
            'name' => 'a-merchant-name',
            'products' => [],
        ];
        $expectedProducts = ['a-product-asin', 'another-product-asin'];

        $this->merchantsCollection->insert($merchantFixture);

        $repositoryDE = new MongoCatalogueRepository($this->environment, 'a-merchant-id', $this->marketplaceDE);
        $repositoryIT = new MongoCatalogueRepository($this->environment, 'a-merchant-id', $this->marketplaceIT);
        $repositoryDE->add('a-product-asin');
        $repositoryDE->add('another-product-asin');
        $repositoryIT->add('a-third-product');

        $products = $repositoryDE->getProductsCatalogue();
        $this->assertEquals($expectedProducts, $products);
    }

    public function test_repository_will_add_two_products_with_same_asin_but_different_marketplace()
    {
        $mongoId = new \MongoId();
        $merchantFixture = [
            '_id' => $mongoId,
            'seller_ids' => [
                'uk' => 'a-merchant-id',
                'de' => 'a-merchant-id',
            ],
            'name' => 'a-merchant-name',
            'products' => [],
        ];
        $expectedMerchantFixtures = [
            $mongoId->__toString() => [
                '_id' => $mongoId,
                'seller_ids' => [
                    'uk' => 'a-merchant-id',
                    'de' => 'a-merchant-id',
                ],
                'name' => 'a-merchant-name',
                'products' => [
                    [
                        'asin' => 'a-product-asin',
                        'active' => true,
                        'marketplace' => 'uk',
                    ],
                    [
                        'asin' => 'a-product-asin',
                        'active' => true,
                        'marketplace' => 'de',
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
            'seller_ids.uk' => 'a-merchant-id'
        ]);

        $this->assertEquals($expectedMerchantFixtures, iterator_to_array($merchantDataCursor));
    }
}
