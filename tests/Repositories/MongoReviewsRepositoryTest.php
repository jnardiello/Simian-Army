<?php

namespace Simian\Repositories;

use Simian\Environment\Environment;
use Simian\Reviews\ReviewBuilder;

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
        $this->reviewsCollection = $mainDb->selectCollection($this->environment->get('mongo.collection.reviews'));
        $this->queueCollection = $mainDb->selectCollection($this->environment->get('mongo.collection.queue'));
        $this->queueRepository = new MongoMailQueueRepository($this->environment);
    }

    public function tearDown()
    {
        $this->reviewsCollection->remove([]);
        $this->queueCollection->remove([]);
    }

    public function test_repository_should_add_new_review_and_push_to_mail_queue()
    {
        $asin = 'an-asin';
        $review = ReviewBuilder::aReviewFromArray([
            '_id' => 'this-is-an-id',
            'seller_id' => 'some_seller_id',
            'seller_name' => 'some_seller_name',
            'product_title' => 'some-product-title',
            'product_link' => 'product-link',
            'verified_purchase' => 'yes',
            'rating' => 'a-review-rating',
            'review_title' => 'a-review-title',
            'review_author' => 'an-author-name',
            'date' => 'some-date',
            'verified-purchase' => 'yes',
            'item_link' => 'http://some-line.com',
            'asin' => $asin,
            'permalink' => 'http://some-permalink.com',
            'text' => 'great product!',
        ]);

        $repository = new MongoReviewsRepository($this->environment, $this->queueRepository);
        $repository->addReviewToAsin($review, $asin);

        $this->assertEquals(1, $this->reviewsCollection->count());
        $this->assertEquals(1, $this->queueCollection->count());
    }

    public function test_repository_should_not_add_two_times_the_same_review()
    {
        $asin = 'an-asin';
        $review = ReviewBuilder::aReviewFromArray([
            '_id' => 'this-is-an-id',
            'seller_id' => 'some_seller_id',
            'seller_name' => 'some_seller_name',
            'product_title' => 'some-product-title',
            'product_link' => 'product-link',
            'verified_purchase' => 'yes',
            'rating' => 'a-review-rating',
            'review_title' => 'a-review-title',
            'review_author' => 'an-author-name',
            'date' => 'some-date',
            'verified-purchase' => 'yes',
            'item_link' => 'http://some-line.com',
            'asin' => $asin,
            'permalink' => 'http://some-permalink.com',
            'text' => 'great product!',
        ]);

        $repository = new MongoReviewsRepository($this->environment, $this->queueRepository);

        for ($i = 0; $i < 10; $i++) {
            $repository->addReviewToAsin($review, $asin);
        }

        $this->assertEquals(1, $this->reviewsCollection->count());
    }

    public function test_can_count_all_reviews_for_a_given_product()
    {
        $asin = 'an-asin';
        $review1 = ReviewBuilder::aReviewFromArray([
            '_id' => 'this-is-an-id',
            'seller_id' => 'some_seller_id',
            'seller_name' => 'some_seller_name',
            'product_title' => 'some-product-title',
            'product_link' => 'product-link',
            'verified_purchase' => 'yes',
            'rating' => 'a-review-rating',
            'review_title' => 'a-review-title',
            'review_author' => 'an-author-name',
            'date' => 'some-date',
            'verified-purchase' => 'yes',
            'item_link' => 'http://some-line.com',
            'asin' => $asin,
            'permalink' => 'http://some-permalink.com',
            'text' => 'great product!',
        ]);

        $review2 = ReviewBuilder::aReviewFromArray([
            '_id' => 'this-is-another-id',
            'seller_id' => 'some_seller_id',
            'seller_name' => 'some_seller_name',
            'product_title' => 'some-product-title',
            'product_link' => 'product-link',
            'verified_purchase' => 'yes',
            'rating' => 'a-review-rating',
            'review_title' => 'a-review-title',
            'review_author' => 'an-author-name',
            'date' => 'some-date',
            'verified-purchase' => 'yes',
            'item_link' => 'http://some-line.com',
            'asin' => $asin,
            'permalink' => 'http://some-permalink.com',
            'text' => 'great product!',
        ]);

        $repository = new MongoReviewsRepository($this->environment, $this->queueRepository);
        $repository->addReviewToAsin($review1, $asin);
        $repository->addReviewToAsin($review2, $asin);

        $this->assertEquals(2, $repository->countReviewsFor($asin));
    }
}
