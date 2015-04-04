<?php

/**
* This file is part of Work Digital's Data Platform.
*
* (c) 2015 Work Digital
*/

namespace Simian;

use Simian\Environment\Environment;
use Simian\Repositories\MongoSellerRepository;

/**
* Class Seller
*
* @author Jacopo Nardiello <jacopo.nardiello@gmail.com>
*/
class Seller
{
    private $sellerIds;

    public function __construct(array $sellerIds, $sellerName, $sellerEmail, array $products)
    {
        $this->sellerIds = $sellerIds;
        $this->name = $sellerName;
        $this->email = $sellerEmail;
        $this->products = $products;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getIds()
    {
        return $this->sellerIds;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function toArray()
    {
        return [
            'seller_ids' => $this->getIds(),
            'name' => $this->getName(),
            'email' => $this->getEmail(),
            'products' => $this->getProducts(),
        ];
    }
}
