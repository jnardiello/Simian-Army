<?php

$env = new \Simian\Environment\Environment('prod');
$queueRepo = new \Simian\Repositories\MongoMailQueueRepository($env);
$sellerRepo = new \Simian\Repositories\MongoSellerRepository($env);
$mailgun = new \Mailgun\Mailgun();

$mailman = new \Simian\Mailman($queueRepo, $sellerRepo, $mailgun);

echo "------- STARTED\n";
$mailman->send();

echo "------- FINISHED\n";
