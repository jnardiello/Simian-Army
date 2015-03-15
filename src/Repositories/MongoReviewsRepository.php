<?php

namespace Simian\Repositories;

/**
 * Class MongoReviewsRepository
 * @author John Doe
 */
class MongoReviewsRepository
{
    public function __construct($environment)
    {
        $client = new \MongoClient($environment->get('mongo.host'));
        $mainDb = $client->selectDB($environment->get('mongo.data.db'));
        $this->reviewsCollection = $mainDb->selectCollection($environment->get('mongo.reviews'));
    }

    public function addReviewToAsin($review)
    {
        $doc = $this->reviewsCollection->findOne(["_id" => $review['_id']]);

        if (empty($doc)) {
            $this->reviewsCollection->insert($review);
        }
    }
}
