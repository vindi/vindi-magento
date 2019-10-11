<?php

class Logger
{
    const ENCRYPTED_PAYMENT_PROFILE_CARD = '{
        {
            "holder_name":"teste",
            "card_expiration":"02\/2020",
            "card_number":"**** *557",
            "card_cvv":"***",
            "customer_id":211154,
            "payment_company_code":"mastercard",
            "payment_method_code":"credit_card"
        }
    }';

    const INVALID_SUBSCRIPTION_RESPONSE = '{
        {
            "errors": [{
                "id": "invalid_parameter",
                "parameter": "plan_id",
                "message": "não encontrado"
            }
        }';
}