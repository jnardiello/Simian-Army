<?php

require __DIR__ . "/../bootstrap.php";

$env = new \Simian\Environment\Environment('prod');
$queueRepo = new \Simian\Repositories\MongoMailQueueRepository($env);
$sellerRepo = new \Simian\Repositories\MongoSellerRepository($env);
$mailgun = new \Mailgun\Mailgun('key-f33b7d4556b361eeba543eeca496654b');

$mailman = new \Simian\Mailman($queueRepo, $sellerRepo, $mailgun);

echo "------- STARTED\n";
$mailman->send();

echo "------- FINISHED\n";
