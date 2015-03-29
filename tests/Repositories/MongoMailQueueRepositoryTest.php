<?php

/**
* This file is part of Work Digital's Data Platform.
*
* (c) 2015 Work Digital
*/

namespace Simian\Repositories;
use Simian\Environment\Environment;


/**
* Class MongoMailQueueRepositoryTest
*
* @author Jacopo Nardiello <jacopo.nardiello@dice.com>
*/
class MongoMailQueueRepositoryTest extends \PHPUnit_Framework_TestCase {
    public function test_repository_should_push_review_into_queue()
    {
        $environment = new Environment('test');
        $repository = new MongoMailQueueRepository($environment);
    }
}
