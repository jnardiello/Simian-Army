<?php

namespace Simian\Environment;

/**
 * Class Environment
 * @author Jacopo Nardiello
 */
class Environment
{
    const ETC_PATH = "etc/";

    private $config;

    public function __construct($env)
    {
        $path = __DIR__ . '/../../' . self::ETC_PATH;
        $configFile = $env . '.properties';

        $this->config = parse_ini_file($path . $configFile);
    }

    public function get($property)
    {
        if (array_key_exists($property, $this->config)) {
            return $this->config[$property];
        }

        return false;
    }
}
