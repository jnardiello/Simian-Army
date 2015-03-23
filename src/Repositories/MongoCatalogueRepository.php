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
        $this->merchantId = $merchantId;
        $client = new \MongoClient($environment->get('mongo.host'));
        $mainDb = $client->selectDB($environment->get('mongo.data.db'));
        $this->collection = $mainDb->selectCollection(
            $environment->get("mongo.merchants")
        );
    }

    public function add($asin)
    {
        $newProduct = [
            'asin' => $asin,
            'active' => true,
        ];
        $this->collection->findAndModify(
            [
                '_id' => $this->merchantId
            ],
            [
                '$push' => [
                    'products' => $newProduct,
                ],
            ]
    );
    }

    public function getProductsCatalogue()
    {
        $catalogueCursor = $this->collection->find([
            '_id' => $this->merchantId
        ]);

        $catalogue = iterator_to_array($catalogueCursor);

        $results = [];
        foreach ($catalogue[$this->merchantId]['products'] as $product) {
            $results[] = $product['asin'];
        }

        return $results;
    }
}
