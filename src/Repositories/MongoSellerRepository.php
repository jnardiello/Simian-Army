<?php

/**
* This file is part of Work Digital's Data Platform.
*
* (c) 2015 Work Digital
*/

namespace Simian\Repositories;

use Prophecy\Exception\Prediction\AggregateException;
use Simian\Environment\Environment;

/**
* Class MongoSellerRepository
*
* @author Jacopo Nardiello <jacopo.nardiello@dice.com>
*/
class MongoSellerRepository
{
    private $collection;

    public function __construct(Environment $environment)
    {
        $client = new \MongoClient($environment->get('mongo.host'));
        $db = $client->selectDB($environment->get('mongo.data.db'));

        $this->collection = $db->selectCollection($environment->get('mongo.merchants'));
    }

    public function findName($sellerId)
    {
        $data = $this->collection->findOne([
            '_id' => $sellerId,
        ]);

        return $data['name'];
    }

    public function findEmail($sellerId)
    {
        $data = $this->collection->findOne([
            '_id' => $sellerId,
        ]);

        return $data['email'];
    }

    public function insertOne(array $sellerData)
    {
        $this->sanitizeData($sellerData);

        $this->collection->insert($sellerData);
    }

    public function remove(array $data)
    {
        $this->collection->remove($data);
    }

    private function sanitizeData($data)
    {
        if (!isset($data['_id'], $data['name'], $data['email'])) {
            throw new \Exception('Malformed data when adding new seller');
        }
    }
}