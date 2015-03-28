<?php

/**
* This file is part of Work Digital's Data Platform.
*
* (c) 2015 Work Digital
*/

namespace Simian\Repositories;

use Simian\Environment\Environment;

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
    }

    protected function tearDown()
    {
        $this->repository->remove([]);
    }

    public function test_repository_can_find_seller_data()
    {
        $this->loadMinotaurFixtures();

        $sellerId = 'A3RFFOCMGATC6W';

        $this->assertEquals('Minotaur', $this->repository->findName($sellerId));
        $this->assertEquals('callum@mediadevil.com', $this->repository->findEmail($sellerId));
    }

    private function loadMinotaurFixtures()
    {
        $minotaurData = [
            '_id' => 'A3RFFOCMGATC6W',
            'name' => 'Minotaur',
            'email' => 'callum@mediadevil.com'
        ];

        $this->repository->insertOne($minotaurData);
    }
}
