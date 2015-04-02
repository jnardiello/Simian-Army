<?php

/**
* This file is part of Work Digital's Data Platform.
*
* (c) 2015 Work Digital
*/

namespace Simian\Repositories;
use Simian\Environment\Environment;


/**
* Class MongoMailQueueRepository
*
* @author Jacopo Nardiello <jacopo.nardiello@dice.com>
*/
class MongoMailQueueRepository
{
    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
        $this->queueCollection = (new \MongoClient($this->environment->get('mongo.host')))
            ->selectDB($this->environment->get('mongo.data.db'))
            ->selectCollection($this->environment->get('mongo.collection.queue'));
    }

    public function push($type, array $dataToQueue)
    {
        $data = [
            'created_at' => time(),
            'type' => $type,
            'payload' => $dataToQueue
        ];

        $this->queueCollection->insert($data);
    }
}
