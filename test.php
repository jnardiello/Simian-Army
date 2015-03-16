<?php

namespace Simian;

require_once 'bootstrap.php';

use GuzzleHttp\Client;
use Simian\Environment\Environment;
use Simian\Repositories\MongoReviewsRepository;

$environment = new Environment('test');
$client = new \MongoClient($environment->get('mongo.host'));
$db = $client->selectDb($environment->get('mongo.data.db'));
$collection = $db->selectCollection($environment->get('mongo.reviews'));
$repository = new MongoReviewsRepository($environment);

$reviewsScraper = new ReviewsScraper(
    $environment,
    new Client(),
    $repository
);

$reviewsScraper->run([
    'B00G26XWDI',
]);
