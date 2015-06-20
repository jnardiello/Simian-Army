<?php

namespace Simian\Environment;

/**
 * @author Jacopo Nardiello
 */
class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->environment = new Environment('test');
    }

    public function testCanRetrieveExistingProperty()
    {
        $mongoDb = $this->environment->get('mongo.queues.db');

        $this->assertEquals('queues', $mongoDb);
    }

    public function testWillGetFalseTryingToGetANonExistingProperty()
    {
        $nonExistingProperty = $this->environment->get('this.is.a.non.existing.property');

        $this->assertFalse($nonExistingProperty);
    }
}
