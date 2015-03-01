<?php

namespace Simian\Repositories;

use Simian\Pages\Page;

class MongoProductPageQueueRepository
{

    const MAX_NUM_MESSAGES_TO_BE_CONSUMED = 10;

    private $client;
    private $db;
    private $collection;

    public function __construct($host, $db, $collection)
    {
        $this->client = new \MongoClient($host);
        $this->db = $this->client->selectDB($db);
        $this->collection = $this->db->selectCollection($collection);
    }

    public function push(Page $page, $filename)
    {
        $productDocument = [
            "asin" => $page->getAsin(),
            "crawled_at" => new \MongoDate($page->getTime()),
            "file" => $filename,
        ];

        $this->collection->insert($productDocument);
    }

    public function consume()
    {
        $results = [];

        for ($i = 0; $i < self::MAX_NUM_MESSAGES_TO_BE_CONSUMED; $i++) {
            $results[] = $this->collection->findAndModify(
                                                [], [], null, ['remove' => true]
                                            );
        }

        return array_filter($results);
    }
}
