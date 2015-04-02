<?php

/**
* This file is part of Work Digital's Data Platform.
*
* (c) 2015 Work Digital
*/

namespace Simian\Repositories;

use Simian\Environment\Environment;
use Simian\Seller;

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
        $this->merchantCollection = $client->selectDB($this->environment->get('mongo.data.db'))
                                     ->selectCollection($this->environment->get('mongo.merchants'));
        $this->expectedMinotaurData = [
            '_id' => 'A3RFFOCMGATC6W',
            'name' => 'Minotaur',
            'email' => 'callum@mediadevil.com',
            'products' => [],
        ];
    }

    protected function tearDown()
    {
        $this->merchantCollection->remove([]);
    }

    public function test_repository_should_create_a_seller()
    {
        $this->loadMinotaurFixtures();

        $sellerId = 'A3RFFOCMGATC6W';
        $seller = $this->repository->findSeller($sellerId);

        $this->assertInstanceOf('Simian\Seller', $seller);
        $this->assertEquals($this->expectedMinotaurData, $seller->toArray());
    }

    public function test_repository_should_return_null_if_seller_does_not_exist()
    {
        $sellerId = 'A3RFFOCMGATC6W';
        $seller = $this->repository->findSeller($sellerId);

        $this->assertNull($seller);
    }

    public function test_repository_should_insert_new_seller()
    {
        $seller = new Seller('a-seller-id', 'a-seller-name', 'a-seller-email', []);

        $this->repository->insertOne($seller);
        $actualSeller = $this->merchantCollection->findOne(['_id' => 'a-seller-id']);

        $this->assertTrue(is_array($actualSeller));
    }

    public function test_repository_insert_should_be_idempotent()
    {
        $seller = new Seller('a-seller-id', 'a-seller-name', 'a-seller-email', []);

        $this->repository->insertOne($seller);
        $this->repository->insertOne($seller);
        $actualSeller = $this->merchantCollection->find(['_id' => 'a-seller-id']);

        $this->assertEquals(1, $actualSeller->count());
    }

    private function loadMinotaurFixtures()
    {
        $this->merchantCollection->insert($this->expectedMinotaurData);
    }
}
