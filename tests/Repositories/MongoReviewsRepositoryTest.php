<?php

namespace Simian\Repositories;

use Simian\Environment\Environment;

/**
 * @author Jacopo Nardiello
 */
class MongoReviewsRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->environment = new Environment('test');
        $client = new \MongoClient($this->environment->get('mongo.host'));
        $mainDb = $client->selectDB($this->environment->get('mongo.data.db'));
        $this->reviewsCollection = $mainDb->selectCollection($this->environment->get('mongo.reviews'));
    }

    public function tearDown()
    {
        $this->reviewsCollection->remove([]);
    }

    public function test_repository_should_add_new_review_to_collection()
    {
        $asin = 'an-asin';
        $review = [
            'rating' => 'a-review-rating',
            'title' => 'a-review-title',
            'author' => 'an-author-name',
            'date' => 'some-date',
            'verified-purchase' => 'yes',
            'item_link' => 'http://some-line.com',
            'asin' => $asin,
            'permalink' => 'http://some-permalink.com',
            'text' => 'great product!',
        ];

        $repository = new MongoReviewsRepository($this->environment);
        $repository->addReviewToAsin($review, $asin);

        $this->assertEquals(1, $this->reviewsCollection->count());
    }

    public function test_repository_should_not_add_two_times_the_same_review()
    {
        $asin = 'an-asin';
        $review = [
            'rating' => 'a-review-rating',
            'title' => 'a-review-title',
            'author' => 'an-author-name',
            'date' => 'some-date',
            'verified-purchase' => 'yes',
            'item_link' => 'http://some-line.com',
            'asin' => $asin,
            'permalink' => 'http://some-permalink.com',
            'text' => 'great product!',
        ];

        $repository = new MongoReviewsRepository($this->environment);
        $repository->addReviewToAsin($review, $asin);
        $repository->addReviewToAsin($review, $asin);

        $this->assertEquals(1, $this->reviewsCollection->count());
    }
}
