<?php

namespace Simian;

use GuzzleHttp\Client;
use Simian\Environment\Environment;
use Simian\Repositories\MongoReviewsRepository;

/**
 * @author Jacopo Nardiello <jacopo.nardiello@gmail.com>
 */
abstract class AbstractScraperTest extends \PHPUnit_Framework_TestCase
{
    protected function getStubbedHttpClient($stubbedHtml)
    {
        // Mocking guzzle
        $client = $this->getMockBuilder('GuzzleHttp\Client')
                       ->disableOriginalConstructor()
                       ->getMock();
        $request = $this->getMockBuilder('GuzzleHttp\Message\Request')
                        ->disableOriginalConstructor()
                        ->getMock();
        $response = $this->getMockBuilder('GuzzleHttp\Message\Response')
                         ->disableOriginalConstructor()
                         ->getMock();
        $htmlStream = $this->getMockBuilder('GuzzleHttp\Stream\Stream')
                           ->disableOriginalConstructor()
                           ->getMock();

        $client->method('createRequest')
               ->willReturn($request);
        $client->method('send')
               ->willReturn($response);
        $response->method('getBody')
                 ->willReturn($stubbedHtml);

        return $client;
    }

    protected function getMailgunStub()
    {
        $mailgun = $this->getMockBuilder('Mailgun\Mailgun')
                        ->setMethods(['sendMessage'])
                        ->getMock();

        return $mailgun;
    }
}
