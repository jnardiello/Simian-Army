<?php

namespace Simian;

require __DIR__ . "/../bootstrap.php";

use Simian\Environment\Environment;
use GuzzleHttp\Client;

$options = getopt("", [
    'seller:'
]);
$seller = $options['seller'];

if (!isset($seller)) {
    throw new \Exception('Please add seller id');
}

$environment = new Environment('prod');
$client = new Client();

$scraper = new CatalogueScraper(
    $environment,
    $client
);

$scraper->run($seller);
