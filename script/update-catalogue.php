<?php

namespace Simian;

require __DIR__ . "/../bootstrap.php";

use Simian\Environment\Environment;
use GuzzleHttp\Client;

$environment = new Environment('prod');
$merchants = ["A1010PM0QYBVOG", "A3RFFOCMGATC6W", "A2CODDGMAUR50T"];
$client = new Client();

$scraper = new CatalogueScraper(
    $environment,
    $client
);

foreach ($merchants as $merchant) {
    $scraper->run($merchant);
}
