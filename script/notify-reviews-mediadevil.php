<?php

namespace Simian;

require __DIR__ . "/../bootstrap.php";

use Simian\Environment\Environment;
use GuzzleHttp\Client;
use Simian\Repositories\MongoCatalogueRepository;
use Simian\Repositories\MongoReviewsRepository;
use Mailgun\Mailgun;

$environment = new Environment('prod');
$mediadevil = "A1010PM0QYBVOG";
$mailgun = new Mailgun('key-f33b7d4556b361eeba543eeca496654b');
$catalogueRepository = new MongoCatalogueRepository($environment, $mediadevil);
$reviewsRepository = new MongoReviewsRepository($environment, $mailgun);
$client = new Client();
$reviewsScraper = new ReviewsScraper(
    $environment,
    $client,
    $reviewsRepository
);

$products = $catalogueRepository->getProductsCatalogue();
$reviewsScraper->run($products);
