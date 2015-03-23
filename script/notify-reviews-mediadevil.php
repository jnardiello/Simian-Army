<?php

namespace Simian;

require __DIR__ . "/../bootstrap.php";

use Simian\Environment\Environment;
use GuzzleHttp\Client;
use Simian\Repositories\MongoCatalogueRepository;
use Simian\Repositories\MongoReviewsRepository;

$environment = new Environment('prod');
$mediadevil = "A1010PM0QYBVOG";
$catalogueRepository = new MongoCatalogueRepository($environment, $mediadevil);
$reviewsRepository = new MongoReviewsRepository($environment);
$client = new Client();
$reviewsScraper = new ReviewsScraper(
    $environment,
    $client,
    $reviewsRepository
);

$products = $catalogueRepository->getProductsCatalogue();
$reviewsScraper->run($products);
