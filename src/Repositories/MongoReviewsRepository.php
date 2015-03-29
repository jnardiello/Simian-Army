<?php

namespace Simian\Repositories;

use Simian\Reviews\Review;

/**
 * Class MongoReviewsRepository
 * @author Jacopo Nardiello
 */
class MongoReviewsRepository
{
    public function __construct($environment)
    {
        $client = new \MongoClient($environment->get('mongo.host'));
        $mainDb = $client->selectDB($environment->get('mongo.data.db'));
        $this->reviewsCollection = $mainDb->selectCollection($environment->get('mongo.reviews'));
    }

    public function addReviewToAsin(Review $review)
    {
        $doc = $this->reviewsCollection->findOne(["_id" => $review->getId()]);

        if (empty($doc)) {
            $this->reviewsCollection->insert($review->toArray());
        }
    }

    public function countReviewsFor($asin)
    {
        return $this->reviewsCollection->count([
            'asin' => $asin,
        ]);
    }
}
