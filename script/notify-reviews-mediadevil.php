<?php

namespace Simian;

require __DIR__ . "/../bootstrap.php";

use Simian\Environment\Environment;
use GuzzleHttp\Client;
use Simian\Repositories\MongoCatalogueRepository;
use Simian\Repositories\MongoReviewsRepository;
use Mailgun\Mailgun;

$environment = new Environment('prod');
$merchants = ["A1010PM0QYBVOG", "A3RFFOCMGATC6W", "A2CODDGMAUR50T"];
$mailgun = new Mailgun('key-f33b7d4556b361eeba543eeca496654b');

foreach ($merchants as $merchant) {
    $catalogueRepository = new MongoCatalogueRepository($environment, $merchant);
    $reviewsRepository = new MongoReviewsRepository($environment, $mailgun);
    $client = new Client();
    $reviewsScraper = new ReviewsScraper(
        $environment,
        $client,
        $reviewsRepository
    );

    $products = $catalogueRepository->getProductsCatalogue();
    $reviewsScraper->run($products);
}
