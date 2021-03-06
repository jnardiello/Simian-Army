<?php

/**
* This file is part of Work Digital's Data Platform.
*
* (c) 2015 Work Digital
*/

namespace Simian\Repositories;

use Simian\Seller;
use Simian\Marketplace;
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

        $this->collection = $db->selectCollection($environment->get('mongo.collection.sellers'));
    }

    public function findByName($sellerName)
    {
        $data = $this->collection->findOne([
            'name' => $sellerName,
        ]);

        if (!isset($data)) {
            return null;
        }

        return new Seller(
            $data['seller_ids'],
            $data['name'],
            $data['email'],
            $data['products']
        );
    }

    public function findById($sellerId, Marketplace $marketplace)
    {
        $data = $this->collection->findOne([
            "seller_ids.{$marketplace->getSlug()}" => $sellerId,
        ]);

        if (!isset($data)) {
            return null;
        }

        return new Seller(
            $data['seller_ids'],
            $data['name'],
            $data['email'],
            $data['products']
        );
    }

    public function insertOne(Seller $seller)
    {
        $alreadyThere = $this->collection->findOne([
            'seller_ids' => $seller->getIds(),
        ]);

        if (!isset($alreadyThere)) {
            $this->collection->insert($seller->toArray());
        }

    }
}
