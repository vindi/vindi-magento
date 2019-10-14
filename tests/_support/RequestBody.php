<?php

class RequestBody
{
    const UNENCRYPTED_PAYMENT_PROFILE_REQUEST = '{
        {
            "holder_name":"teste",
            "card_expiration":"02\/2020",
            "card_number":"5555 5555 5555 5557",
            "card_cvv":"123",
            "customer_id":211154,
            "payment_company_code":"mastercard",
            "payment_method_code":"credit_card"
        }
    }';

    const SAMPLE_SUBSCRIPTION_REQUEST = '{
        {
            "plan_id": 123,
            "customer_id": 456,
            "payment_method_code": "bank_slip",
            "product_items": [
              { 
                "product_id": 789
              }
            ]
          }';

    const INVALID_SUBSCRIPTION_REQUEST = '{
        {
            "plan_id": 1651032,
            "customer_id": 456,
            "payment_method_code": "bank_slip",
            "product_items": [
              { 
                "product_id": 789
              }
            ]
        }';
}
