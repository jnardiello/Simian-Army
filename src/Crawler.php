<?php

namespace Simian;

use Simian\Pages\PageBuilder;
use Simian\Repositories\StorageRepository;
use GuzzleHttp\Client;

class Crawler
{
    private $baseUrl;
    private $asins = [];

    public function __construct($baseUrl, $storagePath)
    {
        $this->baseUrl = $baseUrl;
        $this->client = new Client();
        $this->pageBuilder = new PageBuilder($this->baseUrl);
        $this->storageRepository = new StorageRepository($storagePath);
    }

    public function run(array $asins)
    {
        $writtenFiles = [];

        foreach ($asins as $asin) {
            $page = $this->getPage($asin);
            $this->asins[$asin] = $page;
            $filename = $this->storageRepository->add($page);

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
