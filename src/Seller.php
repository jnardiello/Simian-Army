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
    private $repository;

    public function __construct(Environment $environment, $sellerId)
    {
        $this->repository = new MongoSellerRepository($environment);
        $this->sellerId = $sellerId;
        $this->name = $this->repository->findName($sellerId);
        $this->email = $this->repository->findEmail($sellerId);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function toArray()
    {
        return [
            'id' => $this->sellerId,
            'name' => $this->getName(),
            'email' => $this->getEmail(),
        ];
    }
}