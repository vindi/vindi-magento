<?php

class Responses
{
    const ACTIVE_BANK_SLIP = array(
        'payment_methods' => array(
            array(
                'name'        => 'Boleto bancário',
                'type'        => 'PaymentMethod::BankSlip',
                'status'      => 'active',
                'code'        => 'bank_slip'
            )
        )
    );

    const ACTIVE_CREDIT_CARD = array(
        'payment_methods' => array(
            array(
                'name'        => 'Cartão de Crédito',
                'type'        => 'PaymentMethod::CreditCard',
                'status'      => 'active',
                'code'        => 'credit_card',
                'payment_companies' => array(
                    array(
                        'name' => 'Mastercard',
                        'code' => 'mastercard'
                    ),
                    array(
                        'name' => 'Visa',
                        'code' => 'visa'
                    ),

                    array(
                        'name' => 'American Express',
                        'code' => 'american_express'
                    )
                )
            )
        )
    );

    const ACTIVE_DEBIT_CARD = array(
        'payment_methods' => array(
            array(
                'name'        => 'Cartão de Débito',
                'type'        => 'PaymentMethod::DebitCard',
                'status'      => 'active',
                'code'        => 'debit_card',
                'payment_companies' => array(
                    array(
                        'name' => 'Maestro',
                        'code' => 'maestro'
                    ),
                    array(
                        'name' => 'Visa Electron',
                        'code' => 'visa_electron'
                    ),

                    array(
                        'name' => 'Elo',
                        'code' => 'elo_debit'
                    )
                )
            )
        )
    );

    const GENERAL_PAYMENT_METHODS = array(
        'payment_methods' => array(
            array(
                'name'        => 'Boleto bancário',
                'type'        => 'PaymentMethod::BankSlip',
                'status'      => 'active',
                'code'        => 'bank_slip'
            ),
            array(
                'name'        => 'Cartão de Crédito',
                'type'        => 'PaymentMethod::CreditCard',
                'status'      => 'active',
                'code'        => 'credit_card',
                'payment_companies' => array(
                    array(
                        'name' => 'Mastercard',
                        'code' => 'mastercard'
                    ),
                    array(
                        'name' => 'Visa',
                        'code' => 'visa'
                    ),

                    array(
                        'name' => 'American Express',
                        'code' => 'american_express'
                    )
                )
            ),
            array(
                'name'        => 'Cartão de Débito',
                'type'        => 'PaymentMethod::DebitCard',
                'status'      => 'active',
                'code'        => 'debit_card',
                'payment_companies' => array(
                    array(
                        'name' => 'Maestro',
                        'code' => 'maestro'
                    ),
                    array(
                        'name' => 'Visa Electron',
                        'code' => 'visa_electron'
                    ),

                    array(
                        'name' => 'Elo',
                        'code' => 'elo_debit'
                    )
                )
            )
        )
    );

    const EMPTY_PAYMENT_METHODS = array(
        'payment_methods' => array()
    );

    const SAMPLE_SUBSCRIPTION_RESPONSE = '{
    {
        "subscription":{
            "id":142531,
            "status":"active",
            "start_at":"2019-10-11T00:00:00.000-03:00",
            "end_at":null,
            "next_billing_at":
            "2019-11-11T00:00:00.000-02:00",
            "overdue_since":null,
            "code":"mag-100000063-1570799555",
            "cancel_at":null,
            "interval":"months",
            "interval_count":1,
            "billing_trigger_type":"beginning_of_period",
            "billing_trigger_day":0,
            "billing_cycles":null,
            "installments":1,
            "created_at":"2019-10-11T10:12:40.152-03:00",
            "updated_at":"2019-10-11T10:12:40.152-03:00",
            "customer":{
                "id":211154,
                "name":"teste teste",
                "email":"teste@mail.com",
                "code":"mag-3-1569949129"
            },
            "plan":{
                "id":2242,
                "name":"assinatura mensal",
                "code":null
            },
            "product_items":[
                {
                    "id":249400,
                    "status":"active",
                    "uses":1,
                    "cycles":null,
                    "quantity":1,
                    "created_at":"2019-10-11T10:12:40.000-03:00",
                    "updated_at":"2019-10-11T10:12:40.000-03:00",
                    "product":{
                        "id":27166,
                        "name":"teste",
                        "code":"teste"
                    },
                    "pricing_schema":{
                        "id":129478,
                        "short_format":"R$ 259,00",
                        "price":"259.0",
                        "minimum_price":null,
                        "schema_type":"flat",
                        "pricing_ranges":[],
                        "created_at":"2019-10-11T10:12:40.000-03:00"
                    },
                    "discounts":[]}
                    payment_method":{
                        "id":3206,
                        "public_name":
                        "Cartão de crédito",
                        "name":"Cartão de Crédito",
                        "code":"credit_card",
                        "type":"PaymentMethod::CreditCard"
                    },
                    "current_period":{
                        "id":2341352,
                        "billing_at":"2019-10-11T00:00:00.000-03:00",
                        "cycle":1,
                        "start_at":"2019-10-11T00:00:00.000-03:00",
                        "end_at":"2019-11-10T23:59:59.000-02:00",
                        "duration":2674799
                    },
                    "metadata":{},
                        "payment_profile":{
                            "id":158796,
                            "holder_name":"TESTE",
                            "registry_code":null,
                            "bank_branch":null,
                            "bank_account":null,
                            "card_expiration":"2020-01-31T23:59:59.000-02:00",
                            "card_number_first_six":"555555",
                            "card_number_last_four":"5557",
                            "token":"f9901a42-e6ca-4156-a1e0-693d7da2894a",
                            "created_at":"2019-10-11T10:12:38.000-03:00",
                            "payment_company":{
                                "id":18,
                                "name":"Mastercard",
                                "code":"mastercard"
                            }
                        },
                        "invoice_split":false
                    },
                    "bill":{
                        "id":2337376,
                        "code":null,
                        "amount":"264.0",
                        "installments":1,
                        "status":"pending",
                        "billing_at":null,
                        "due_at":"2019-10-14T23:59:59.000-03:00",
                        "url":"https://sandbox-app.vindi.com.br/customer/bills/12345",
                        "created_at":"2019-10-11T10:12:40.000-03:00",
                        "charges":[
                            {
                                "id":2293846,
                                "amount":"264.0",
                                "status":"paid",
                                "due_at":"2019-10-14T23:59:59.000-03:00",
                                "paid_at":null,
                                "installments":1,
                                "attempt_count":1,
                                "next_attempt":null,
                                "print_url":null,
                                "created_at":"2019-10-11T10:12:40.000-03:00",
                                "updated_at":"2019-10-11T10:12:40.000-03:00",
                                "last_transaction":{
                                    "id":1453084,
                                    "transaction_type":"capture",
                                    "status":"success",
                                    "amount":"264.0",
                                    "installments":1,
                                    "gateway_message":null,
                                    "gateway_response_code":null,
                                    "gateway_authorization":"",
                                    "gateway_transaction_id":null,
                                    "fraud_detector_score":null,
                                    "fraud_detector_status":null,
                                    "fraud_detector_id":null,
                                    "created_at":"2019-10-11T10:12:40.000-03:00",
                                    "gateway":{
                                        "id":1694,
                                        "connector":"cielo_v3"
                                    },
                                    "payment_profile":{
                                        "id":158796,
                                        "holder_name":"TESTE",
                                        "registry_code":null,
                                        "bank_branch":null,
                                        "bank_account":null,
                                        "card_expiration":"2020-01-31T23:59:59.000-02:00",
                                        "card_number_first_six":"555555",
                                        "card_number_last_four":"5557",
                                        "token":"f9901a42-e6ca-4156-a1e0-693d7da2894a",
                                        "created_at":"2019-10-11T10:12:38.000-03:00",
                                        "payment_company":{
                                            "id":18,
                                            "name":"Mastercard",
                                            "code":"mastercard"
                                            }
                                        }
                                    },
                                    "payment_method":{
                                        "id":3206,
                                        "public_name":
                                        "Cartão de crédito",
                                        "name":"Cartão de Crédito",
                                        "code":"credit_card",
                                        "type":"PaymentMethod::CreditCard"
                                    }
                                }
                            ],
                            "payment_profile":{
                                "id":158796,
                                "holder_name":"TESTE",
                                "registry_code":null,
                                "bank_branch":null,
                                "bank_account":null,
                                "card_expiration":"2020-01-31T23:59:59.000-02:00",
                                "card_number_first_six":"555555",
                                "card_number_last_four":"5557",
                                "token":"f9901a42-e6ca-4156-a1e0-693d7da2894a",
                                "created_at":"2019-10-11T10:12:38.000-03:00",
                                "payment_company":{
                                    "id":18,
                                    "name":"Mastercard",
                                    "code":"mastercard"
                                }
                            }
                        }
                    }
    }';

    const INVALID_SUBSCRIPTION_RESPONSE =
        "Erro em subscriptions: invalid_parameter: plan - inválido";
}