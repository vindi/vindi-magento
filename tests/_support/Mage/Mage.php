<?php

class Mage
{
    public static function getStoreConfig($params)
    {
        if ('vindi_subscription/general/api_key' == $params)
            return getenv('VINDI_API_KEY');
    }

    public static function getUrl(...$params)
    {
        return 'http://vindi.magento';
    }

    public static function helper($params)
    {
        return __CLASS__;
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
}
