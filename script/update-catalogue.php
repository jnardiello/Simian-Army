<?php

namespace Simian;

require __DIR__ . "/../bootstrap.php";

use Simian\Environment\Environment;
use GuzzleHttp\Client;
use Simian\Repositories\MongoSellerRepository;

$options = getopt("", [
    'seller:',
    'marketplace:'
]);
$sellerId = $options['seller'];
$marketplacePlaceholder = $options['marketplace'];

if (!isset($sellerId)) {
    throw new \Exception('Please add seller id');
}

$environment = new Environment('prod');
$client = new Client();
$sellerRepository = new MongoSellerRepository($environment);
$marketplace = new Marketplace($marketplacePlaceholder, $environment);

$seller = $sellerRepository->findSeller($sellerId);

if (!isset($seller)) {
    echo "Seller not present in the collection.\n\n";
    echo "Seller name: ";
    $sellerName = trim(fgets(STDIN));
    echo "Seller email: ";
    $sellerEmail = trim(fgets(STDIN));

    $seller = new Seller($sellerId, $sellerName, $sellerEmail, []);
    $sellerRepository->insertOne($seller);
}

$scraper = new CatalogueScraper(
    $environment,
    $client,
    $marketplace
);

$scraper->run($sellerId);
