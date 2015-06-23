<?php

/**
* This file is part of Work Digital's Data Platform.
*
* (c) 2015 Work Digital
*/

namespace Simian;
use Mailgun\Mailgun;
use Simian\Repositories\MongoMailQueueRepository;
use Simian\Repositories\MongoSellerRepository;


/**
* Class Mailman
*
* @author Jacopo Nardiello <jacopo.nardiello@dice.com>
*/
class Mailman
{
    const DOMAIN = 'simian.army';
    const FROM = 'rainforest@simian.army';
    const SUBJECT = 'You received a new negative review for ';
    const TYPE = 'send_review_email';

    private $queueRepo;
    private $sellerRepo;
    private $mailClient;


    public function __construct(MongoMailQueueRepository $queueRepository, MongoSellerRepository $sellerRepo, Mailgun $mailgun)
    {
        $this->queueRepo = $queueRepository;
        $this->sellerRepo = $sellerRepo;
        $this->mailClient = $mailgun;

    }

    public function send()
    {
        $cursor = $this->queueRepo->findAll();

        foreach ($cursor as $doc) {
            $seller = $this->sellerRepo->findByName($doc['payload']['seller_name'])->toArray();
            if ($doc['type'] == static::TYPE && $doc['payload']['rating'] <= 3) {
                $message = [
                    'from' => static::FROM,
                    'to' => $seller['email'] . ", jacopo@workdigital.co.uk",
                    'subject' => static::SUBJECT . $doc['payload']['product_title'],
                    'text' => "You have just received a negative review. \nCheck: \n" . $doc['payload']['permalink'],
                ];

                $this->mailClient->sendMessage(static::DOMAIN, $message);
            }

            $this->queueRepo->remove([
                '_id' => $doc['_id'],
            ]);
        }
    }
}