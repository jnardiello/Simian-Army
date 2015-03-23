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

    public function test_repository_should_add_new_review_to_collection_and_send_email()
    {
        $mailgun = $this->getMockBuilder('Mailgun\Mailgun')
                        ->getMock();
        $mailgun->expects($this->once())
                 ->method('sendMessage');

        $asin = 'an-asin';
        $review = [
            '_id' => 'this-is-an-id',
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

        $repository = new MongoReviewsRepository($this->environment, $mailgun);
        $repository->addReviewToAsin($review, $asin);

        $this->assertEquals(1, $this->reviewsCollection->count());
    }

    public function xtest_repository_should_not_add_two_times_the_same_review()
    {
        $mailgun = $this->getMockBuilder('Mailgun\Mailgun')
                        ->getMock();
        $mailgun->expects($this->once())
                 ->method('sendMessage');

        $asin = 'an-asin';
        $review = [
            '_id' => 'this-is-an-id',
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

        $repository = new MongoReviewsRepository($this->environment, $mailgun);

        for ($i = 0; $i < 10; $i++) {
            $repository->addReviewToAsin($review, $asin);
        }

        $this->assertEquals(1, $this->reviewsCollection->count());
    }

    public function xtest_can_count_all_reviews_for_a_given_product()
    {
        $mailgun = $this->getMockBuilder('Mailgun\Mailgun')
                        ->getMock();
        $mailgun->expects($this->exactly(2))
                 ->method('sendMessage');

        $asin = 'an-asin';
        $review1 = [
            '_id' => 'this-is-an-id',
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

        $review2 = [
            '_id' => 'another-id',
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

        $repository = new MongoReviewsRepository($this->environment, $mailgun);
        $repository->addReviewToAsin($review1, $asin);
        $repository->addReviewToAsin($review2, $asin);

        $this->assertEquals(2, $repository->countReviewsFor($asin));
    }
}
