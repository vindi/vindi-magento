<?php

class Mage
{
    public $version;

    public static function getStoreConfig($params)
    {
        if ('vindi_subscription/general/api_key' == $params)
            return getenv('VINDI_API_KEY');
    }

    public static function getConfig()
    {
        return new Mage();
    }

    public static function getUrl(...$params)
    {
        return 'http://vindi.magento';
    }

    public static function helper($params)
    {
        return new Mage();
    }

    public static function getHash(...$params)
    {
        return 'vindi.magento';
    }

    public static function throwException()
    {
        return 'error';
    }

    public static function log()
    {
        return true;
    }

    public static function getModuleConfig($params)
    {
        return new Mage();
    }

    public static function getKey()
    {
        return new Mage();
    }

    public static function app()
    {
        return new Mage();
    }

    public static function getCache()
    {
        return new Mage();
    }

    public static function load()
    {
        return false;
    }

    public static function save()
    {
        return true;
    }

    public static function addError()
    {
        return true;
    }

    public static function getSingleton()
    {
        return true;
    }
}
