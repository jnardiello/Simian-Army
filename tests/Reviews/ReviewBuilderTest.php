<?php

/**
* This file is part of Work Digital's Data Platform.
*
* (c) 2015 Work Digital
*/

namespace Simian\Reviews;


/**
* Class ReviewBuilderTest
*
* @author Jacopo Nardiello <jacopo.nardiello@dice.com>
*/
class ReviewBuilderTest extends \PHPUnit_Framework_TestCase {
    public function test_review_builder_should_return_review()
    {
        $expectedReview = [
            'seller_id' => 'a-seller-id',
            'seller_name' => 'a-seller-name',
            'product_title' => 'a-product-title',
            'product_link' => 'a-product-link',
            '_id' => 'a-review-id',
            'rating' => 5,
            'review_title' => 'a-review-title',
            'review_author' => 'a-review-author',
            'asin' => 'a-product-asin',
            'date' => 'some-date',
            'verified_purchase' => 'is-verified',
            'permalink' => 'some-permalink',
            'text' => 'a-review-text',
            'marketplace' => 'it',
        ];
        $review = ReviewBuilder::aReview()
                                ->havingSeller('a-seller-id', 'a-seller-name')
                                ->forProduct('a-product-title', 'a-product-link')
                                ->withId('a-review-id')
                                ->with('rating', 5)
                                ->with('asin', 'a-product-asin')
                                ->with('date', 'some-date')
                                ->with('verified_purchase', 'is-verified')
                                ->with('permalink', 'some-permalink')
                                ->with('text', 'a-review-text')
                                ->with('review_title', 'a-review-title')
                                ->with('review_author', 'a-review-author')
                                ->with('marketplace', 'it')
                                ->build();

        $this->assertInstanceOf('Simian\Reviews\Review', $review);
        $this->assertEquals($expectedReview, $review->toArray());
    }

    public function test_review_builder_should_return_review_from_array()
    {
        $expectedReview = [
            'seller_id' => 'a-seller-id',
            'seller_name' => 'a-seller-name',
            'product_title' => 'a-product-title',
            'product_link' => 'a-product-link',
            '_id' => 'a-review-id',
            'rating' => 5,
            'review_title' => 'a-review-title',
            'review_author' => 'a-review-author',
            'asin' => 'a-product-asin',
            'date' => 'some-date',
            'verified_purchase' => 'is-verified',
            'permalink' => 'some-permalink',
            'text' => 'a-review-text',
            'marketplace' => 'it',
        ];

        $review = ReviewBuilder::aReviewFromArray($expectedReview);

        $this->assertInstanceOf('Simian\Reviews\Review', $review);
        $this->assertEquals($expectedReview, $review->toArray());
    }
}
