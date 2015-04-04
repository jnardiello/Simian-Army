<?php

namespace Simian;

require __DIR__ . "/../bootstrap.php";

use Simian\Environment\Environment;
use GuzzleHttp\Client;
use Simian\Repositories\MongoCatalogueRepository;
use Simian\Repositories\MongoMailQueueRepository;
use Simian\Repositories\MongoReviewsRepository;
use Mailgun\Mailgun;
use Simian\Repositories\MongoSellerRepository;

/* $options = getopt("", [ */
/*     'seller:', */
/* ]); */
/* $sellerId = $options['seller']; */

$marketplaceId = "uk";
$seller = [
    'mediadevil' => "A1010PM0QYBVOG",
];
$sellerId = $seller['mediadevil'];

    /* "A3RFFOCMGATC6W", */
    /* "A2CODDGMAUR50 */

// SETUP
$environment = new Environment('prod');
$catalogueRepository = new MongoCatalogueRepository($environment, $sellerId);
$reviewsRepository = new MongoReviewsRepository(
    $environment,
    (new MongoMailQueueRepository($environment))
);
$sellerRepository = new MongoSellerRepository($environment);

// CONTROLLER
$seller = $sellerRepository->findSeller($sellerId);
$client = new Client();
$reviewsScraper = new ReviewsScraper(
    $environment,
    $client,
    $reviewsRepository
);
$products = $catalogueRepository->getProductsCatalogue();

$reviewsScraper->run($seller, $products);
