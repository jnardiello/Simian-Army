<?php

# Include the Autoloader (see "Libraries" for install instructions)
require 'vendor/autoload.php';
use Mailgun\Mailgun;

# Instantiate the client.
$mgClient = new Mailgun('key-f33b7d4556b361eeba543eeca496654b');
$domain = "simian.army";

# Make the call to the client.
$result = $mgClient->sendMessage($domain, array(
    'from'    => 'Excited User <jacopo.nardiello@gmail.com>',
    'to'      => 'jacopo.nardiello@gmail.com',
    'subject' => 'Hello',
    'text'    => 'Testing some Mailgun awesomness!',
    'html'    => '<html><h1>HTML version of the body</h1></html>'
));
