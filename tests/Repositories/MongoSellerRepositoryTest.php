<?php

/**
* This file is part of Work Digital's Data Platform.
*
* (c) 2015 Work Digital
*/

namespace Simian\Repositories;

use Simian\Environment\Environment;
use Simian\Seller;
use Simian\Marketplace;

/**
* Class MongoSellerRepositoryTest
*
* @author Jacopo Nardiello <jacopo.nardiello@dice.com>
*/
class MongoSellerRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->environment = new Environment('test');
        $this->repository = new MongoSellerRepository($this->environment);
        $client = new \MongoClient($this->environment->get('mongo.host'));
        $this->sellersCollection = $client->selectDB($this->environment->get('mongo.data.db'))
                                     ->selectCollection($this->environment->get('mongo.collection.sellers'));
        $this->expectedMinotaurData = [
            'seller_ids' => [
                'uk' => 'A3RFFOCMGATC6W'
            ],
            'name' => 'Minotaur',
            'email' => 'callum@mediadevil.com',
            'products' => [],
        ];
        $this->marketplace = new Marketplace('uk', $this->environment);
    }

    protected function tearDown()
    {
        $this->sellersCollection->remove([]);
    }

    public function test_repository_should_return_a_seller_when_searching_by_id()
    {
        $this->loadMinotaurFixtures();

        $sellerId = 'A3RFFOCMGATC6W';
        $seller = $this->repository->findById($sellerId, $this->marketplace);

        $this->assertInstanceOf('Simian\Seller', $seller);
        $this->assertEquals($this->expectedMinotaurData, $seller->toArray());
    }

    public function test_repository_should_return_a_seller_when_searching_by_name()
    {
        $this->loadMinotaurFixtures();

        $sellerName = 'Minotaur';
        $seller = $this->repository->findByName($sellerName);

        $this->assertInstanceOf('Simian\Seller', $seller);
        $this->assertEquals($this->expectedMinotaurData, $seller->toArray());
    }

    public function test_repository_should_return_null_if_seller_does_not_exist()
    {
        $sellerId = 'A3RFFOCMGATC6W';
        $seller = $this->repository->findById($sellerId, $this->marketplace);

        $this->assertNull($seller);
    }

    public function test_repository_should_insert_new_seller()
    {
        $seller = new Seller(
            [
                'uk' => 'a-seller-id',
            ],
            'a-seller-name',
            'a-seller-email',
            []
        );

        $this->repository->insertOne($seller);
        $actualSeller = $this->sellersCollection->findOne(['seller_ids.uk' => 'a-seller-id']);

        $this->assertTrue(is_array($actualSeller));
    }

    public function test_repository_insert_should_be_idempotent()
    {
        $seller = new Seller(['uk' => 'a-seller-id'], 'a-seller-name', 'a-seller-email', []);

        $this->repository->insertOne($seller);
        $this->repository->insertOne($seller);
        $actualSeller = $this->sellersCollection->find(['seller_ids.uk' => 'a-seller-id']);

        $this->assertEquals(1, $actualSeller->count());
    }

    private function loadMinotaurFixtures()
    {
        $this->sellersCollection->insert($this->expectedMinotaurData);
        unset($this->expectedMinotaurData['_id']); // _id is set by the driver
    }
}
