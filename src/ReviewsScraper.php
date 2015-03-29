<?php

namespace Simian;

use GuzzleHttp\Client;
use Simian\Environment\Environment;
use Simian\Repositories\MongoReviewsRepository;
use Simian\Reviews\ReviewBuilder;
use Simian\Seller;
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

    public function __construct(Environment $environment, Client $client, MongoReviewsRepository $repository)
    {
        $this->environment = $environment;
        $this->client = $client;
        $this->repository = $repository;
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
        /* var_dump("Scraping {$asin}"); */
        $stream = $this->getHtmlStream($url);
        $crawler = new Crawler((string) $stream);
        $this->mainProductLink = $this->exists('(//h1/div/a/@href)[1]', $crawler);

        // Checking number of review pages that we actually need to crawl
        if (!isset($currentDepth, $maxDepth)) {
            $currentDepth = 0;
            $numPages = $this->getNumPagesToCrawl($asin, $crawler);
            $maxDepth = (isset($numPages)) ? $numPages : 1; // If for some reason this isn't defined we scan only the current page
        }

        if ($currentDepth == $maxDepth) {
            return ;
        }

        $crawler->filterXPath('//table[@id="productReviews"]//td/div')
                ->each(function($doc) use ($asin){
                    $review['_id'] = $this->extractIdFromPermalink($this->exists('(//div/span/a/@href)[1]', $doc));
                    $review['rating'] = $this->prettyRating($this->exists('(//div//span/@class)[1]', $doc));
                    $review['product_title'] = $this->prettyProductTitle($this->exists('(//div/b)[1]', $doc));
                    $review['product_link'] = $this->assignLink($this->exists('(//div/b/a/@href)[1]', $doc));
                    $review['review_title'] = $this->exists('(//b)[1]', $doc);
                    $review['review_author'] = $this->exists('(//div/a[1])[1]', $doc);
                    $review['date'] = new \MongoDate(strtotime($this->exists('(//nobr)[1]', $doc)));
                    $review['verified_purchase'] = $this->exists('//span[@class="crVerifiedStripe"]', $doc);
                    $review['asin'] = $asin;
                    $review['permalink'] = $this->exists('(//div/span/a/@href)[1]', $doc);
                    $review['text'] = $this->exists('//div[@class="reviewText"]', $doc);
                    $review['seller_id'] = $this->seller->getId();
                    $review['seller_name'] = $this->seller->getName();

                    $review = ReviewBuilder::aReviewFromArray($review);

                    $this->repository->addReviewToAsin($review, $asin);
            });

        $nextLink = $crawler->filterXPath("(//span[@class='paging']/a[contains(text(), 'Next ›')]/@href)[1]");
        if ($nextLink->count()) {
            $this->persistReviewsPage($asin, $nextLink->text(), ++$currentDepth, $maxDepth);
        }
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
        return $this->environment->get('product.uk.base.url') .
               $asin .
               '?sortBy=bySubmissionDateDescending';

    }

    private function getNumPagesToCrawl($asin, $crawler)
    {
        $numCurrentReviews = $this->exists("(//table[@id='productSummary']//b)[1]", $crawler);
        if (isset($numCurrentReviews)) {
            $regex = '/^([0-9,]+).*$/i';
            preg_match($regex, $numCurrentReviews, $matches);

            // Need to sanitize reviews with thousands of reviews
            // format '3,704' -> 3704 int
            $currentTotReviews = (int) str_replace(',', '', $matches[1]);
            $alreadyStoreRepositories = $this->repository->countReviewsFor($asin);

            return ceil(($currentTotReviews - $alreadyStoreRepositories)/10);
        }
    }
}
