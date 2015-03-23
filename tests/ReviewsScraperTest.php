<?php

namespace Simian;

use GuzzleHttp\Client;
use Simian\Environment\Environment;
use Simian\Repositories\MongoReviewsRepository;

/**
 * @author Jacopo Nardiello <jacopo.nardiello@gmail.com>
 */
class ReviewsScraperTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->environment = new Environment('test');
        $client = new \MongoClient($this->environment->get('mongo.host'));
        $db = $client->selectDb($this->environment->get('mongo.data.db'));
        $this->collection = $db->selectCollection($this->environment->get('mongo.reviews'));
        $mailgun = $this->getMockBuilder('Mailgun\Mailgun')
                        ->setMethods(['sendMessage'])
                        ->getMock();
        $this->repository = new MongoReviewsRepository($this->environment, $mailgun);
    }

    public function tearDown()
    {
        $this->collection->remove([]);
    }

    public function test_can_scrape_product_page_and_persist_reviews()
    {
        $stubbedHtml = file_get_contents(__DIR__ . "/fixtures/html/reviews.html");

        // Mocking guzzle
        $client = $this->getMockBuilder('GuzzleHttp\Client')
                       ->disableOriginalConstructor()
                       ->getMock();
        $request = $this->getMockBuilder('GuzzleHttp\Message\Request')
                        ->disableOriginalConstructor()
                        ->getMock();
        $response = $this->getMockBuilder('GuzzleHttp\Message\Response')
                         ->disableOriginalConstructor()
                         ->getMock();
        $htmlStream = $this->getMockBuilder('GuzzleHttp\Stream\Stream')
                           ->disableOriginalConstructor()
                           ->getMock();

        $client->method('createRequest')
               ->willReturn($request);
        $client->method('send')
               ->willReturn($response);
        $response->method('getBody')
                 ->willReturn($stubbedHtml);
        $reviewsScraper = new ReviewsScraper(
            $this->environment,
            $client,
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

        // Mocking guzzle
        $client = $this->getMockBuilder('GuzzleHttp\Client')
                       ->disableOriginalConstructor()
                       ->getMock();
        $request = $this->getMockBuilder('GuzzleHttp\Message\Request')
                        ->disableOriginalConstructor()
                        ->getMock();
        $response = $this->getMockBuilder('GuzzleHttp\Message\Response')
                         ->disableOriginalConstructor()
                         ->getMock();
        $htmlStream = $this->getMockBuilder('GuzzleHttp\Stream\Stream')
                           ->disableOriginalConstructor()
                           ->getMock();

        $client->expects($this->exactly(2))
               ->method('createRequest')
               ->willReturn($request);
        $client->method('send')
               ->willReturn($response);
        $response->method('getBody')
            ->willReturn($stubbedHtml);

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
}
