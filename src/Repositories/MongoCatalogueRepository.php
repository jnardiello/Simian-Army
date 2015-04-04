<?php

namespace Simian\Repositories;

use Simian\Environment\Environment;
use Simian\Marketplace;

class MongoCatalogueRepository
{
    private $environment;
    private $merchantId;
    private $collection;

    public function __construct(Environment $environment, $merchantId, Marketplace $marketplace)
    {
        $this->environment = $environment;
        $this->merchantId = $merchantId;
        $this->marketplace = $marketplace;
        $client = new \MongoClient($environment->get('mongo.host'));
        $mainDb = $client->selectDB($environment->get('mongo.data.db'));
        $this->collection = $mainDb->selectCollection(
            $environment->get("mongo.collection.merchants")
        );
    }

    public function add($asin)
    {
        $alreadyInCatalogue = false;
        $newProduct = [
            'asin' => $asin,
            'active' => true,
            'marketplace' => $this->marketplace->getId(),
        ];

        $seller = $this->collection->findOne(['_id' => $this->merchantId]);

        foreach ($seller['products'] as $product) {
            if ($product['asin'] == $asin && $product['marketplace'] == $this->marketplace->getId()) {
                $alreadyInCatalogue = true;
            }
        }

        if (!$alreadyInCatalogue) {
            $this->collection->findAndModify(
                [
                    '_id' => $this->merchantId
                ],
                [
                    '$push' => [
                        'products' => $newProduct,
                    ],
                ]);
        }
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
