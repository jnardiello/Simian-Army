<?php

namespace Simian\Repositories;

use Simian\Pages\Page;

class MongoProductPageQueueRepository
{
    private $client;
    private $db;
    private $collection;

    public function __construct($db, $collection)
    {
        $this->client = new \MongoClient();
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
}
