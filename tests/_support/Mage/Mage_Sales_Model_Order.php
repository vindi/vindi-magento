<?php

class Mage_Sales_Model_Order
{
    public static function canInvoice()
    {
        return true;
    }

    public static function getStatusLabel()
    {
        return 'pending payment';
    }
}
