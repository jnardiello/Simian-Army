<?php

use GuzzleHttp\Client;

require 'vendor/autoload.php';



$client = new Client();
$request = $client->createRequest('GET', 'http://www.foo.com');
$response = $client->send($request);

$code = $response->getStatusCode();
$body = $response->getBody(true);
var_dump((string) $body);
die();
