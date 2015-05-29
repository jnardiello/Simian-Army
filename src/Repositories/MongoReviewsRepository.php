<?php

namespace Simian\Repositories;

use Simian\Reviews\Review;
use Simian\Marketplace;

/**
 * Class MongoReviewsRepository
 * @author Jacopo Nardiello
 */
class MongoReviewsRepository
{
    public function __construct($environment, MongoMailQueueRepository $queueRepository)
    {
        $client = new \MongoClient($environment->get('mongo.host'));
        $mainDb = $client->selectDB($environment->get('mongo.data.db'));
        $this->reviewsCollection = $mainDb->selectCollection($environment->get('mongo.collection.reviews'));
        $this->queueRepository = $queueRepository;
    }

    public function addReviewToAsin(Review $review)
    {
        $doc = $this->reviewsCollection->findOne(["_id" => $review->getId()]);

        if (empty($doc)) {
            $this->reviewsCollection->insert($review->toArray());
            $this->queueRepository->push('send_review_email', $review->toArray());
        }
    }

    public function countReviewsFor($asin, Marketplace $marketplace)
    {
        return $this->reviewsCollection->count([
            'asin' => $asin,
            'marketplace' => $marketplace->getSlug(),
        ]);
    }
}
