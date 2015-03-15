<?php

namespace Simian;

use GuzzleHttp\Client;
use Simian\Environment\Environment;

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
    }

    public function tearDown()
    {
        $this->collection->remove([]);
    }

    public function testCanScrapeProductPageAndPersistReviews()
    {
        $reviewsScraper = new ReviewsScraper(
            $this->environment,
            new Client()
        );

        $reviewsScraper->run([
            'B00RXILJ16',
        ]);

        $this->assertEquals(36, $this->collection->count());
    }
}
