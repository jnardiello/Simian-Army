<?php

namespace Simian;

use Simian\Environment\Environment;

/**
 * Class Marketplace
 * @author Jacopo Nardiello <jacopo.nardiello@gmail.com>
 */
class Marketplace
{
    private $environment;
    private $marketplacePlaceholder;

    public function __construct($marketplacePlaceholder, $environment)
    {
        $this->environment = $environment;
        $this->marketplacePlaceholder = $marketplacePlaceholder;
    }

    public function getBaseUrl()
    {
        return $this->environment->get("{$this->marketplacePlaceholder}.main.base.url");
    }

    public function getId()
    {
        return $this->environment->get("{$this->marketplacePlaceholder}.marketplace.id");
    }

    public function getCatalogueBaseUrl()
    {
        return $this->environment->get("{$this->marketplacePlaceholder}.catalogue.base.url");
    }

    public function getSlug()
    {
        return $this->marketplacePlaceholder;
    }
}
