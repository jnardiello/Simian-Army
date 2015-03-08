<?php

namespace Simian\Repositories;

use Simian\Environment\Environment;

class MongoCatalogueRepository
{
    private $environment;
    private $merchantId;
    private $collection;

    public function __construct(Environment $environment, $merchantId)
    {
        $this->environment = $environment;
        $this->asin = $merchantId;
        $client = new \MongoClient($environment->get('mongo.host'));
        $mainDb = $client->selectDB($environment->get('mongo.data.db'));
        $this->collection = $mainDb->selectCollection(
            $environment->get("mongo.merchants")
        );
    }

    public function add($asin)
    {
        $this->collection->findAndModify(
            [
                '_id' => $this->asin
            ],
            [
                '$push' => [
                    'products' => $asin,
                ],
            ]
    );
    }
}
