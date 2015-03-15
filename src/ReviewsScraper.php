<?php

namespace Simian;

use GuzzleHttp\Client;
use Simian\Environment\Environment;
use Simian\Repositories\MongoReviewsRepository;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class ReviewsScraper
 * @author Jacopo Nardiello <jacopo.nardiello@gmail.com>
 */
class ReviewsScraper
{
    private $environment;
    private $client;

    public function __construct(Environment $environment, Client $client, MongoReviewsRepository $repository)
    {
        $this->environment = $environment;
        $this->client = $client;
        $this->repository = $repository;
    }

    public function run(array $asins = [])
    {
        foreach ($asins as $asin) {
            $url = $this->buildRequestUrl($asin);

            $this->persistReviewsPage($asin, $url);
        }
    }

    private function persistReviewsPage($asin, $url)
    {
        $stream = $this->getHtmlStream($url);
        $crawler = new Crawler((string) $stream);
        $reviewsList = $crawler->filterXPath('//table[@id="productReviews"]//td/div')
            ->each(function($doc) use ($asin){
                $review['_id'] = $this->extractIdFromPermalink($this->exists('(//div/span/a/@href)[1]', $doc));
                $review['rating'] = $this->exists('(//div//span/@class)[1]', $doc);
                $review['title'] = $this->exists('(//b)[1]', $doc);
                $review['author'] = $this->exists('(//div/a[1])[1]', $doc);
                $review['date'] = new \MongoDate(strtotime($this->exists('(//nobr)[1]', $doc)));
                $review['verified-purchase'] = $this->exists('//span[@class="crVerifiedStripe"]', $doc);
                $review['item_link'] = $this->exists('(//b/a/@href)[1]', $doc);
                $review['asin'] = $asin;
                $review['permalink'] = $this->exists('(//div/span/a/@href)[1]', $doc);
                $review['text'] = $this->exists('//div[@class="reviewText"]', $doc);

                $this->repository->addReviewToAsin($review, $asin);
            });

        $nextLink = $crawler->filterXPath("(//span[@class='paging']/a[contains(text(), 'Next ›')]/@href)[1]");
        if ($nextLink->count()) {
            $this->persistReviewsPage($asin, $nextLink->text());
        }
    }

    private function extractIdFromPermalink($url)
    {
        $regex = '/.*\/(R[A-Z0-9]+)\/.*/i';
        preg_match($regex, $url, $matches);

        if (!isset($matches[1])) {
            throw new \Exception('Couldnt find id for review');
        }
            
        return $reviewId = $matches[1];
    }

    private function exists($xpath, $doc) {
        if ($doc->filterXPath($xpath)->count()) {
            return $doc->filterXPath($xpath)->text();
        }

        return null;
    }

    private function getHtmlStream($url)
    {
        $request = $this->client->createRequest('GET', $url);
        $response = $this->client->send($request);
        $bodyStream = $response->getBody(true);

        return $bodyStream;
    }

    private function buildRequestUrl($asin)
    {
        // http://www.amazon.co.uk/product-reviews/B00RXIK98K?sortBy=bySubmissionDateDescending
        return $this->environment->get('product.uk.base.url') .
               $asin .
               '?sortBy=bySubmissionDateDescending';

    }
}
