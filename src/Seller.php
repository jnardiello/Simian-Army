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
    private $sellerId;

    public function __construct($sellerId, $sellerName, $sellerEmail, array $products)
    {
        $this->sellerId = $sellerId;
        $this->name = $sellerName;
        $this->email = $sellerEmail;
        $this->products = $products;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getId()
    {
        return $this->sellerId;
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
            '_id' => $this->sellerId,
            'name' => $this->getName(),
            'email' => $this->getEmail(),
            'products' => $this->getProducts(),
        ];
    }
}
