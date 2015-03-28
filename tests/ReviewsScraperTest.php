<?php

namespace Simian;

use GuzzleHttp\Client;
use Simian\Environment\Environment;
use Simian\Repositories\MongoReviewsRepository;

/**
 * @author Jacopo Nardiello <jacopo.nardiello@gmail.com>
 */
class ReviewsScraperTest extends AbstractScraperTest
{
    public function setUp()
    {
        $this->environment = new Environment('test');
        $client = new \MongoClient($this->environment->get('mongo.host'));
        $db = $client->selectDb($this->environment->get('mongo.data.db'));
        $this->collection = $db->selectCollection($this->environment->get('mongo.reviews'));
        $this->repository = new MongoReviewsRepository(
                                    $this->environment, 
                                    $this->getMailgunStub()
                                );
    }

    public function tearDown()
    {
        $this->collection->remove([]);
    }

    public function test_can_scrape_product_page_and_persist_reviews()
    {
        $stubbedHtml = file_get_contents(__DIR__ . "/fixtures/html/reviews.html");

        $reviewsScraper = new ReviewsScraper(
            $this->environment,
            $this->getStubbedHttpClient($stubbedHtml),
            $this->repository
        );

        $reviewsScraper->run([
            'a-test-asin',
        ]);

        $this->assertEquals(10, $this->collection->count());
    }

    public function test_scraper_should_scrape_just_required_pages()
    {
        $stubbedHtml = file_get_contents(__DIR__ . "/fixtures/html/reviews2.html");
        $client = $this->getStubbedHttpClient($stubbedHtml);
        $client->expects($this->exactly(2)) // This test a bit more strict and behavioral
               ->method('createRequest');

        $reviewsScraper = new ReviewsScraper(
            $this->environment,
            $client,
            $this->repository
        );

        $reviewsJsonString = file_get_contents(__DIR__ . "/fixtures/json/reviews.json");
        $reviewsFixtures = json_decode($reviewsJsonString, true);

        // loading fixtures
        foreach ($reviewsFixtures as $fixture) {
            $this->collection->insert($fixture);
        }

        $reviewsScraper->run([
            'a-test-asin',
        ]);
    }

    public function test_scraper_should_select_product_link_from_review()
    {
        $stubbedHtml = file_get_contents(__DIR__ . "/fixtures/html/review-link.html");
        $reviewsScraper = new ReviewsScraper(
            $this->environment,
            $this->getStubbedHttpClient($stubbedHtml),
            $this->repository
        );

        $reviewsScraper->run([
            'a-test-asin',
        ]);

        $persistedReview = $this->collection->findOne();
        $this->assertEquals('http://www.amazon.co.uk/Minotaur-Screen-Protector-iPhone-Protectors-Matte/dp/B00OVI1H2C/ref=cm_cr_pr_orig_subj', $persistedReview['product_link']);
        $this->assertEquals('Minotaur Matte Anti Glare Screen Protector Pack for Apple iPhone 5/5S/5C (6 Screen Protectors) (Electronics)', $persistedReview['product_title']);
    }

    public function test_scraper_should_select_product_link_from_review_with_default_link()
    {
        $stubbedHtml = file_get_contents(__DIR__ . "/fixtures/html/review-no-link.html");
        $reviewsScraper = new ReviewsScraper(
            $this->environment,
            $this->getStubbedHttpClient($stubbedHtml),
            $this->repository
        );

        $reviewsScraper->run([
            'a-test-asin',
        ]);

        $persistedReview = $this->collection->findOne();
        $this->assertEquals('http://www.amazon.co.uk/Minotaur-Screen-Protector-iPhone-Protectors-Matte/dp/B00OVI1H2C/ref=cm_cr_pr_product_top', $persistedReview['product_link']);
        $this->assertEquals('Minotaur Matte Anti Glare Screen Protector Pack for Samsung Galaxy S5 (6 Screen Protectors) (Electronics)', $persistedReview['product_title']);
    }
}
