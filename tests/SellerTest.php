<?php

/**
* This file is part of Work Digital's Data Platform.
*
* (c) 2015 Work Digital
*/

namespace Simian;

use Simian\Environment\Environment;

/**
* Class SellerTest
*
* @author Jacopo Nardiello <jacopo.nardiello@gmail.com>
*/
class SellerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->environment = new Environment('test');
        $client = new \MongoClient($this->environment->get('mongo.host'));
        $db = $client->selectDb($this->environment->get('mongo.data.db'));
        $this->collection = $db->selectCollection($this->environment->get('mongo.merchants'));

        $this->collection->insert([
            '_id' => 'A3RFFOCMGATC6W',
            'name' => 'Minotaur Accessories',
            'email' => 'callum@mediadevil.com',
        ]);
    }

    public function tearDown()
    {
        $this->collection->remove([]);
    }

    public function test_can_retrieve_seller_data()
    {
        $seller = new Seller($this->environment, 'A3RFFOCMGATC6W');
        $expectedSeller = [
            'id' => 'A3RFFOCMGATC6W',
            'name' => 'Minotaur Accessories',
            'email' => 'callum@mediadevil.com',
        ];

        $this->assertEquals($expectedSeller, $seller->toArray());
    }
}