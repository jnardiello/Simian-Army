<?php

/**
* This file is part of Work Digital's Data Platform.
*
* (c) 2015 Work Digital
*/

namespace Simian\Reviews;

use Simian\Marketplace;

/**
* Class Review
*
* @author Jacopo Nardiello <jacopo.nardiello@dice.com>
*/
class Review
{
    private $id;
    private $sellerId;
    private $marketplace;
    private $sellerName;
    private $productTitle;
    private $productLink;
    private $data = [];

    public function setSeller($sellerId, $sellerName)
    {
        $this->sellerId = $sellerId;
        $this->sellerName = $sellerName;
    }

    public function setMarketplace(Marketplace $marketplace)
    {
        $this->marketplace = $marketplace;
    }

    public function getMarketplace()
    {
        return $this->marketplace;
    }

    public function setProduct($productTitle, $productLink)
    {
        $this->productTitle = $productTitle;
        $this->productLink = $productLink;
    }

    public function setId($reviewId)
    {
        $this->id = $reviewId;
    }

    public function setProperty($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function getId()
    {
        return $this->id;
    }

    public function toArray()
    {
        $result = [
            'seller_id' => $this->sellerId,
            'seller_name' => $this->sellerName,
            'product_title' => $this->productTitle,
            'product_link' => $this->productLink,
            '_id' => $this->id,
        ];

        foreach ($this->data as $property => $value) {
            $result[$property] = $value;
        }

        return $result;
    }
}
