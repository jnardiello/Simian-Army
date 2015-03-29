<?php

/**
* This file is part of Work Digital's Data Platform.
*
* (c) 2015 Work Digital
*/

namespace Simian\Reviews;


/**
* Class ReviewBuilder
*
* @author Jacopo Nardiello <jacopo.nardiello@dice.com>
*/
class ReviewBuilder
{
    private $review;

    private function __construct()
    {
        $this->review = new Review();
    }

    public static function aReview()
    {
        return new self;
    }

    public function havingSeller($sellerId, $sellerName)
    {
        $this->review->setSeller($sellerId, $sellerName);
        return $this;
    }

    public function forProduct($productTitle, $productLink)
    {
        $this->review->setProduct($productTitle, $productLink);
        return $this;
    }

    public function withId($reviewId)
    {
        $this->review->setId($reviewId);
        return $this;
    }

    public function with($propertyName, $propertyValue)
    {
        $this->review->setProperty($propertyName, $propertyValue);
        return $this;
    }

    public static function aReviewFromArray($review)
    {
        $review = self::aReview()
            ->havingSeller($review['seller_id'], $review['seller_name'])
            ->forProduct($review['product_title'], $review['product_link'])
            ->withId($review['_id'])
            ->with('rating', $review['rating'])
            ->with('asin', $review['asin'])
            ->with('date', $review['date'])
            ->with('verified_purchase', $review['verified_purchase'])
            ->with('permalink', $review['permalink'])
            ->with('text', $review['text'])
            ->with('review_title', $review['review_title'])
            ->with('review_author', $review['review_author'])
            ->build();

        return $review;
    }

    public function build()
    {
        return $this->review;
    }
}