<?php

namespace Simian;

require __DIR__ . "/../bootstrap.php";

use Simian\Environment\Environment;
use GuzzleHttp\Client;
use Simian\Repositories\MongoSellerRepository;
use Simian\Seller;

$environment = new Environment('prod');
$repository = new MongoSellerRepository($environment);

$seller = new Seller(
    [
        'us' => 'A5PHJ2ILWP16M',
        'uk' => 'A1010PM0QYBVOG',
        'it' => 'A1010PM0QYBVOG',
        'de' => 'A1010PM0QYBVOG',
        'fr' => 'A1010PM0QYBVOG',
    ],
    'MediaDevil',
    'jacopo.nardiello@gmail.com',
    []
);

$repository->insertOne($seller);

echo "\nDone.\n";
