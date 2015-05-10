<?php

namespace Simian;

use Simian\Environment\Environment;

/**
 * @author Jacopo Nardiello <jacopo.nardiello@gmail.com>
 */
class MarketplaceTest extends \PHPUnit_Framework_TestCase
{
    public function test_can_build_marketplace_from_property_file()
    {
        $environment = new Environment('test');
        $marketplace = new Marketplace('uk', $environment);

        $this->assertEquals("http://amazon.co.uk", $marketplace->getBaseUrl());
        $this->assertEquals("A1F83G8C2ARO7P", $marketplace->getId());
        $this->assertEquals("uk", $marketplace->getSlug());
    }
}
