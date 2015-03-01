<?php

namespace Simian;

use Simian\Pages\PageBuilder;
use Simian\Repositories\StorageRepository;
use Simian\Repositories\MongoProductPageQueueRepository;
use Simian\Environment\Environment;
use GuzzleHttp\Client;

class Crawler
{
    const MONGO_COLLECTION = "product_pages_queue";

    private $baseUrl;
    private $asins = [];
    private $client;
    private $storagePath;
    private $pageBuilder;
    private $storageRepository;
    private $mongoProductPageQueueRepository;
    private $urlPostParams;

    public function __construct($baseUrl, Environment $environment)
    {
        $this->baseUrl = $baseUrl;
        $this->client = new Client();
        $this->storagePath = $environment->get('storage.path');

        $this->pageBuilder = new PageBuilder($this->baseUrl);
        $this->storageRepository = new StorageRepository($this->storagePath);
        $this->mongoProductPageQueueRepository = new MongoProductPageQueueRepository(
                                                        $environment->get('mongo.host'),
                                                        $environment->get('mongo.queues.db'),
                                                        self::MONGO_COLLECTION
                                                     );
    }

    public function sortByMostRecent()
    {
        $this->urlPostParams .= "sortBy=bySubmissionDateDescending";

        return $this;
    }

    public function run(array $asins)
    {
        $writtenFiles = [];

        foreach ($asins as $asin) {
            $page = $this->getPage($asin);
            $this->asins[$asin] = $page;
            $filename = $this->storageRepository->add($page);
            if ($filename) {
                $this->mongoProductPageQueueRepository->push($page, $filename);
            }

            $writtenFiles[] = $filename;
        }

        return $writtenFiles;
    }

    private function buildRequestUrl($asin)
    {
        // amazon.it/product-reviews/<ASIN>?<post params>
        return $this->baseUrl . 
               $asin .
               "?".
               $this->urlPostParams;
    }

    private function getPage($asin)
    {
        $htmlStream = $this->getHtmlStream($asin);

        $page = $this->pageBuilder->setAsin($asin)
                                  ->setBody($htmlStream)
                                  ->build();

        return $page;
    }

    private function getHtmlStream($asin)
    {
        var_dump($this->buildRequestUrl($asin));
        die();
        $request = $this->client->createRequest('GET', $this->buildRequestUrl($asin));
        $response = $this->client->send($request);
        $bodyStream = $response->getBody(true);

        return $bodyStream;
    }
}
