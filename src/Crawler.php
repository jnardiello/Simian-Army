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
    private $environment;

    public function __construct($baseUrl, Environment $environment)
    {
        $this->environment = $environment;
        $this->baseUrl = $baseUrl;
        $this->client = new Client();
        $this->pageBuilder = new PageBuilder($this->baseUrl);
        $this->storageRepository = new StorageRepository($environment->get('storage.path'));
        $this->mongoProductPageQueueRepository = new MongoProductPageQueueRepository(
            $environment->get('mongo.host'),
            $environment->get('mongo.queues.db'),
            self::MONGO_COLLECTION
        );
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
        $request = $this->client->createRequest('GET', $this->baseUrl . $asin);
        $response = $this->client->send($request);
        $bodyStream = $response->getBody(true);

        return $bodyStream;
    }
}
