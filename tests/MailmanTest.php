<?php

namespace Simian;

use Simian\Environment\Environment;
use Simian\Repositories\MongoMailQueueRepository;
use Simian\Repositories\MongoSellerRepository;

class MailmanTest extends AbstractScraperTest
{
    private $queueRepository;
    private $mailgun;

    public function setUp()
    {
        $env = new Environment('test');
        $this->queueRepository = new MongoMailQueueRepository($env);
        $this->sellerRepository = new MongoSellerRepository($env);
        $this->mailgun = $this->getMockBuilder('Mailgun\Mailgun')
            ->setMethods(['sendMessage'])
            ->getMock();
    }

    public function tearDown()
    {
        $this->queueRepository->remove();
    }

    public function test_should_send_email_for_message_with_rating_less_than_three()
    {
        $data = [
            "seller_id" => "A1010PM0QYBVOG",
            "seller_name" => "MediaDevil",
            "product_title" => "MediaDevil iPhone 6 Tempered Glass Screen Protector - Crystal Clear (Invisible) - (1 x Protector) (Wireless Phone Accessory)",
            "product_link" => "http=>//www.amazon.co.uk/MediaDevil-iPhone-Tempered-Screen-Protector/dp/B00NLH3M1I/ref=cm_cr_pr_product_top/277-7443630-0386916",
            "_id" => "R1JDQXFMERPTAX",
            "rating" => 3,
            "asin" => "B00NLH3M1I",
            "verified_purchase" => "Verified Purchase(What is this?)",
            "permalink" => "http=>//www.amazon.co.uk/review/R1JDQXFMERPTAX/ref=cm_cr_pr_perm/?ie=UTF8&ASIN=B00NLH3M1I",
            "text" => "Really fantastic product. I was worried at first because when I applied the screen cover to my brand new iPhone 6 as per the instructions there was a huge air bubble BUT, as it said in the information, within 24 hours it had completely disappeared and the screen is clear as , well, glass! I'm a very happy customer and would definitely buy products again by MediaDevil.",
            "review_title" => "An outstanding screen protector",
            "review_author" => "Booklover99",
            "marketplace" => "uk"
        ];
        $this->mailgun->expects($this->once())
            ->method('sendMessage');

        // Setup
        $this->queueRepository->push('send_review_email', $data);
        $mailman = new Mailman(
            $this->queueRepository,
            $this->sellerRepository,
            $this->mailgun
        );

        $mailman->send();
    }

    public function test_should_consume_message_with_good_rating_without_sending_message()
    {
        $data = [
            "seller_id" => "A1010PM0QYBVOG",
            "seller_name" => "MediaDevil",
            "product_title" => "MediaDevil iPhone 6 Tempered Glass Screen Protector - Crystal Clear (Invisible) - (1 x Protector) (Wireless Phone Accessory)",
            "product_link" => "http=>//www.amazon.co.uk/MediaDevil-iPhone-Tempered-Screen-Protector/dp/B00NLH3M1I/ref=cm_cr_pr_product_top/277-7443630-0386916",
            "_id" => "R1JDQXFMERPTAX",
            "rating" => 5,
            "asin" => "B00NLH3M1I",
            "verified_purchase" => "Verified Purchase(What is this?)",
            "permalink" => "http=>//www.amazon.co.uk/review/R1JDQXFMERPTAX/ref=cm_cr_pr_perm/?ie=UTF8&ASIN=B00NLH3M1I",
            "text" => "Really fantastic product. I was worried at first because when I applied the screen cover to my brand new iPhone 6 as per the instructions there was a huge air bubble BUT, as it said in the information, within 24 hours it had completely disappeared and the screen is clear as , well, glass! I'm a very happy customer and would definitely buy products again by MediaDevil.",
            "review_title" => "An outstanding screen protector",
            "review_author" => "Booklover99",
            "marketplace" => "uk"
        ];
        $this->mailgun->expects($this->never())
            ->method('sendMessage');

        // Setup
        $this->queueRepository->push('send_review_email', $data);
        $mailman = new Mailman(
            $this->queueRepository,
            $this->sellerRepository,
            $this->mailgun
        );

        $mailman->send();

        $this->assertEquals(0, $this->queueRepository->count());
    }
}
