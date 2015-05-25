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
            $environment->get("mongo.collection.sellers")
        );
    }

    public function add($asin)
    {
        $alreadyInCatalogue = false;
        $newProduct = [
            'asin' => $asin,
            'active' => true,
            'marketplace' => $this->marketplace->getSlug(),
        ];

        $seller = $this->collection->findOne([
            "seller_ids.{$this->marketplace->getSlug()}" => $this->merchantId
        ]);

        if (!empty($seller['products'])) {
            foreach ($seller['products'] as $product) {
                if (
                    $product['asin'] == $asin && 
                    $product['marketplace'] == $this->marketplace->getSlug()
                ) {
                    $alreadyInCatalogue = true;
                }
            }
        }

        if (!$alreadyInCatalogue) {
            $this->collection->findAndModify(
                [
                    "seller_ids.{$this->marketplace->getSlug()}" => $this->merchantId
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
        $catalogue = $this->collection->findOne([
            "seller_ids.{$this->marketplace->getSlug()}" => $this->merchantId
        ]);
        $results = [];

        foreach ($catalogue['products'] as $product) {
            if ($product['marketplace'] == $this->marketplace->getSlug()) {
                $results[] = $product['asin'];
            }
        }

        return $results;
    }
}
