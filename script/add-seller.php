<?php

namespace Simian;

require __DIR__ . "/../bootstrap.php";

use Simian\Environment\Environment;
use GuzzleHttp\Client;
use Simian\Repositories\MongoSellerRepository;

$seller = [
    'name' => '',
    'id' => [],
    'email' => '',
];

// NAME
while (empty($seller['name'])) {
    echo "Seller name: ";
    $seller['name'] = trim(fgets(STDIN));
}

// EMAIL
while (empty($seller['email'])) {
    echo "Seller contact email: ";
    $seller['email'] = trim(fgets(STDIN));
}


echo "MARKETPLACES INFO\n--------------------\n";
// IDs
while (true) {
    echo "Marketplace ID: ";
    $marketplace = trim(fgets(STDIN));

    if (empty($marketplace)) {
        break;
    }

    echo "Seller ID: ";
    $input = trim(fgets(STDIN));

    if (!empty($input)) {
        $seller['id'][$marketplace] = $input;
    }
}

/* $environment = new Environment('prod'); */
/* $client = new Client(); */
/* $sellerRepository = new MongoSellerRepository($environment); */
/* $marketplace = new Marketplace($marketplacePlaceholder, $environment); */

/* $seller = $sellerRepository->findSeller($sellerId); */

/* if (!isset($seller)) { */
/*     echo "Seller not present in the collection.\n\n"; */
/*     echo "Seller name: "; */
/*     $sellerName = trim(fgets(STDIN)); */
/*     echo "Seller email: "; */
/*     $sellerEmail = trim(fgets(STDIN)); */

/*     $seller = new Seller($sellerId, $sellerName, $sellerEmail, []); */
/*     $sellerRepository->insertOne($seller); */
/* } */
