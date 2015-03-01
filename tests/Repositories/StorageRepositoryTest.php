<?php

namespace Simian\Repositories;

use Simian\Pages\PageBuilder;
use GuzzleHttp\Stream\Stream;

class StorageRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->storagePath = __DIR__ . "/../storage/";
    }

    public function tearDown()
    {
        exec("rm -Rf " . $this->storagePath . "*");
    }

    public function testRepositoryCanPersistPage()
    {
        $asin = "B0058DXA4W";
        $htmlStream = Stream::factory('test page');

        $pageBuilder = new PageBuilder();
        $page = $pageBuilder->setAsin($asin)
                            ->setBody($htmlStream)
                            ->build();

        // ASIN-TIME().html
        $expectedFilename = $this->storagePath . $asin . "-" . $page->getTime() . ".html";

        $repository = new StorageRepository($this->storagePath);
        $repository->add($page);

        $this->assertTrue(file_exists($expectedFilename));
    }
}
