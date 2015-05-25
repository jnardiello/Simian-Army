<?php

namespace Simian;

require __DIR__ . "/../bootstrap.php";

use Simian\Environment\Environment;
use GuzzleHttp\Client;
use Simian\Repositories\MongoSellerRepository;

$options = getopt("", [
    'name:',
]);

if (!isset($options['name'])) {
    echo "\n\nPlease, add --name option\n\n";
}
$sellerName = $options['name'];

$environment = new Environment('prod');
$client = new Client();
$sellerRepository = new MongoSellerRepository($environment);

$seller = $sellerRepository->findByName($sellerName);

if (!isset($seller)) {
    echo "\n\nSeller not present in the collection.\n\n";
    die();
}

foreach ($seller->getIds() as $placeholder => $id) {
    $marketplace = new Marketplace($placeholder, $environment);
    $scraper = new CatalogueScraper(
        $environment,
        $client,
        $marketplace
    );

    $scraper->run($id);
}
