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
    public function setUp()
    {
        $this->environment = new Environment('test');
        $this->queueCollection = (new \MongoClient($this->environment->get('mongo.host')))
            ->selectDB($this->environment->get('mongo.data.db'))
            ->selectCollection($this->environment->get('mongo.queue'));
    }

    public function tearDown()
    {
        $this->queueCollection->remove([]);
    }

    public function test_repository_should_push_review_into_queue()
    {
        $repository = new MongoMailQueueRepository($this->environment);
        $dataToPush = [
            'some-review-name' => 'some-data',
            'some-other-data' => 'some-data',
        ];
        $expectedPushedData = [
            'created_at' => time(),
            'type' => 'send_review_email',
            'payload' => $dataToPush,
        ];

        $repository->push('send_review_email', $dataToPush);
        $actualData = $this->queueCollection->findOne();
        unset($actualData['_id']);

        $this->assertEquals($expectedPushedData, $actualData);
    }
}
