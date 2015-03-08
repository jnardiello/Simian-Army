<?php

namespace Simian\Repositories;

use Simian\Environment\Environment;

class MongoCatalogueRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->environment = new Environment('test');
        $client = new \MongoClient($this->environment->get('mongo.host'));
        $mainDb = $client->selectDB($this->environment->get('mongo.data.db'));
        $this->merchantsCollection = $mainDb->selectCollection("merchants");
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
                    'a-product-asin',
                ]
            ]
        ];
        $this->merchantsCollection->insert($merchantFixture);

        $repository = new MongoCatalogueRepository($this->environment, 'a-merchant-id');
        $productId = 'a-product-asin';

        $repository->add($productId);

        $merchantData = $this->merchantsCollection->find([
            '_id' => 'a-merchant-id'
        ]);
        $this->assertEquals($expectedMerchantFixtures, iterator_to_array($merchantData));
    }
}
