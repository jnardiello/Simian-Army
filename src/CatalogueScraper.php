<?php

namespace Simian;

use Simian\Environment\Environment;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Simian\Repositories\MongoCatalogueRepository;

/**
 * Class CatalogueScraper
 * @author Jacopo Nardiello <jacopo.nardiello@gmail.com>
 */
class CatalogueScraper
{
    private $environment;
    private $client;

    public function __construct(Environment $environment, Client $client)
    {
        $this->environment = $environment;
        $this->client = $client;
    }

    public function run($merchantId, $url = null)
    {
        $this->repository = new MongoCatalogueRepository($this->environment, $merchantId);

        if (isset($merchantId) && !isset($url)) {
            $url = $this->buildRequestUrl($merchantId);
        }

        $stream = $this->getHtmlStream($url);
        $crawler = new Crawler((string) $stream);

        $productsList = $crawler->filterXPath('//div[@id="resultsCol"]//li/@data-asin')
                                ->each(function($document) {
                                    $asin = $document->text();
                                    $this->repository->add($asin);
                                });

        $nextLink = $crawler->filterXPath('(//a[@id="pagnNextLink"]/@href)[1]');
        if ($nextLink->count()) {
            $this->run($merchantId, 'http://amazon.co.uk' . $nextLink->text());
        }
    }

    private function getHtmlStream($url)
    {
        $request = $this->client->createRequest('GET', $url);
        $response = $this->client->send($request);
        $bodyStream = $response->getBody(true);

        return $bodyStream;
    }

    private function buildRequestUrl($merchantId)
    {
        // amazon.co.uk/gp/node/?marketplaceID=A1F83G8C2ARO7P&merchant=A1010PM0QYBVOG
        return $this->environment->get('catalogue.base.url') .
               'marketplaceID=' . $this->environment->get('marketplace.uk.id') .
               "&".
               'merchant=' . $merchantId;
    }
}
