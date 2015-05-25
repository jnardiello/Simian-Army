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

$environment = new Environment('prod');
$client = new Client();
$sellerRepository = new MongoSellerRepository($environment);

/**
 * Getting Name & Marketplace from I
 */
$options = getopt("", [
    'name:',
    'marketplace:'
]);

if (!isset($options['name']) || !isset($options['marketplace'])) {
    echo "\n\nPlease, add --name and a --marketplace option\n\n";
}
$sellerName = $options['name'];
$marketplace = new Marketplace($options['marketplace'], $environment);

$seller = $sellerRepository->findByName($sellerName);
$sellerIds = $seller->getIds();

// Generating Repositories for products and reviews
$catalogueRepository = new MongoCatalogueRepository(
    $environment, 
    $sellerIds[$marketplace->getSlug()], 
    $marketplace
);
$reviewsRepository = new MongoReviewsRepository(
    $environment,
    (new MongoMailQueueRepository($environment))
);

// CONTROLLER
$reviewsScraper = new ReviewsScraper(
    $environment,
    $client,
    $reviewsRepository
);
$products = $catalogueRepository->getProductsCatalogue();

$reviewsScraper->run($seller, $products);
