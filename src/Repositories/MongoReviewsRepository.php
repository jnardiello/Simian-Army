<?php

namespace Simian\Repositories;

use Mailgun\Mailgun;

/**
 * Class MongoReviewsRepository
 * @author John Doe
 */
class MongoReviewsRepository
{
    public function __construct($environment, Mailgun $mailgun)
    {
        $this->mailgun = $mailgun;
        $client = new \MongoClient($environment->get('mongo.host'));
        $mainDb = $client->selectDB($environment->get('mongo.data.db'));
        $this->reviewsCollection = $mainDb->selectCollection($environment->get('mongo.reviews'));
    }

    public function addReviewToAsin($review)
    {
        $doc = $this->reviewsCollection->findOne(["_id" => $review['_id']]);

        if (empty($doc)) {
            $this->reviewsCollection->insert($review);
            /* $domain = "simian.army"; */
            /* $html = "<h1>A new review was added</h1><br /><div>Title: {$review['title']}<br />Author: {$review['author']}<br />Product: <a href='http://www.amazon.co.uk/dp/{$review['asin']}'>{$review['asin']}</a><br /><b>Review:</b><br />{$review['text']}<br /><a href='{$review['permalink']}'>Link</a></div>"; */

            /* # Make the call to the client. */
            /* $result = $this->mailgun->sendMessage($domain, array( */
            /*     'from'    => "Simian General <simian.general@simian.army>", */
            /*     'to'      => "jacopo.nardiello@gmail.com", */
            /*     'subject' => "{$review['rating']} stars review", */
            /*     'html'    => $html */
            /* )); */
        }
    }

    public function countReviewsFor($asin)
    {
        return $this->reviewsCollection->count([
            'asin' => $asin,
        ]);
    }
}
