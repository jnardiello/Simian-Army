<?php

namespace Simian;

require __DIR__ . "/../bootstrap.php";

use Simian\Environment\Environment;
use GuzzleHttp\Client;

$environment = new Environment('prod');
$mediadevil = "A1010PM0QYBVOG";
$client = new Client();

$scraper = new CatalogueScraper(
    $environment,
    $client
);

$scraper->run($mediadevil);
