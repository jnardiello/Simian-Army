<?php

namespace Simian;

use GuzzleHttp\Client;
use Simian\Environment\Environment;
use Simian\Repositories\MongoReviewsRepository;
use Simian\Reviews\ReviewBuilder;
use Simian\Seller;
use Simian\Marketplace;
use Symfony\Component\DomCrawler\Crawler;
use Mailgun\Mailgun;

/**
 * Class ReviewsScraper
 * @author Jacopo Nardiello <jacopo.nardiello@gmail.com>
 */
class ReviewsScraper
{
    private $environment;
    private $client;
    private $starsMapper = [
        'swSprite s_star_5_0 ' => 5,
        'swSprite s_star_4_0 ' => 4,
        'swSprite s_star_3_0 ' => 3,
        'swSprite s_star_2_0 ' => 2,
        'swSprite s_star_1_0 ' => 1,
    ];
    private $template;

    public function __construct(Environment $environment, Client $client, MongoReviewsRepository $repository, Marketplace $marketplace)
    {
        $this->environment = $environment;
        $this->client = $client;
        $this->repository = $repository;
        $this->marketplace = $marketplace;
        $this->template = require __DIR__ . '/../templates/' . $this->marketplace->getSlug() . ".php";
    }

    public function run(Seller $seller, array $asins = [])
    {
        $this->seller = $seller;
        foreach ($asins as $asin) {
            $url = $this->buildRequestUrl($asin);

            $this->persistReviewsPage($asin, $url);
        }
    }

    private function persistReviewsPage($asin, $url, $currentDepth = null, $maxDepth = null)
    {
        $stream = $this->getHtmlStream($url);
        $crawler = new Crawler((string) $stream);
        $this->mainProductLink = $this->exists($this->template['main_product_link'], $crawler);

        // Checking number of review pages that we actually need to crawl
        if (!isset($currentDepth, $maxDepth)) {
            $currentDepth = 0;
            $numPages = $this->getNumPagesToCrawl($asin, $crawler);
            $maxDepth = (isset($numPages)) ? $numPages : 1; // If for some reason this isn't defined we scan only the current page
        }

        if ($currentDepth == $maxDepth) {
            return ;
        }

        $crawler->filterXPath($this->template['context'])
                ->each(function($doc) use ($asin, $crawler){
                    if ($this->marketplace->getSlug() != 'us') {
                        $review['_id'] = $this->extractIdFromPermalink($this->exists($this->template['_id'], $doc));
                        $review['rating'] = $this->prettyRating($this->exists($this->template['rating'], $doc));
                        $review['product_title'] = $this->prettyProductTitle($this->exists($this->template['product_title'], $doc));
                        $review['product_link'] = $this->assignLink($this->exists($this->template['product_link'], $doc));
                        $review['permalink'] = $this->exists($this->template['permalink'], $doc);
                        $review['date'] = new \MongoDate(strtotime($this->exists($this->template['date'], $doc)));
                    } else {
                        $review['_id'] = $this->exists($this->template['_id'], $doc);
                        $review['rating'] = (int) $this->exists($this->template['rating'], $doc);
                        $review['product_title'] = $this->exists($this->template['product_title'], $crawler);
                        $review['product_link'] = $this->normalizeUrl($this->exists($this->template['product_link'], $crawler));
                        $review['permalink'] = $this->normalizeUrl($this->exists($this->template['permalink'], $doc));
                        $review['date'] = new \MongoDate(strtotime(substr($this->exists($this->template['date'], $doc), 3)));
                    }
                    $review['review_title'] = $this->exists($this->template['review_title'], $doc);
                    $review['review_author'] = $this->exists($this->template['review_author'], $doc);
                    $review['verified_purchase'] = $this->exists($this->template['verified_purchase'], $doc);
                    $review['asin'] = $asin;
                    $review['text'] = $this->exists($this->template['text'], $doc);
                    $review['seller_id'] = $this->seller->getOriginalId();
                    $review['seller_name'] = $this->seller->getName();
                    $review['marketplace'] = $this->marketplace->getSlug();

                    $review = ReviewBuilder::aReviewFromArray($review);

                    $this->repository->addReviewToAsin($review, $asin);
            });

        $nextLink = $this->normalizeUrl($this->exists($this->template['next'], $crawler));

        if (isset($nextLink)) {
            $this->persistReviewsPage($asin, $nextLink, ++$currentDepth, $maxDepth);
        }
    }

    private function normalizeUrl($url)
    {
        if (strpos($url, 'http') === false) {
            return $this->marketplace->getBaseUrl() . $url;
        }

        return $url;
    }

    private function prettyProductTitle($text)
    {
        return str_replace("This review is from: ", "", $text);
    }

    private function assignLink($link)
    {
        if (!isset($link)) {
            return $this->mainProductLink;
        }

        return $link;
    }

    private function prettyRating($rating)
    {
        return $this->starsMapper[$rating];
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
        return $this->environment->get("{$this->marketplace->getSlug()}.product.base.url") .
               $asin .
               '?sortBy=bySubmissionDateDescending';

    }

    private function getNumPagesToCrawl($asin, $crawler)
    {
        $numCurrentReviews = $this->exists($this->template['num_reviews'], $crawler);
        if (isset($numCurrentReviews)) {
            $regex = '/^([0-9,]+).*$/i';
            preg_match($regex, $numCurrentReviews, $matches);


            // format '3,704' -> 3704 int
            $currentTotReviews = (int) str_replace(',', '', $matches[1]);
            $alreadyStoredReviews = $this->repository->countReviewsFor($asin, $this->marketplace);

            $pagesToCrawl = ceil(($currentTotReviews - $alreadyStoredReviews)/10);

            if ($pagesToCrawl >= 0) {
                return $pagesToCrawl;
            }

            throw new \Exception('Database is not consistent with page total reviews');
        }
    }
}
