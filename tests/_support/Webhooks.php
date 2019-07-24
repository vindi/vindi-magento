<?php

class Webhooks
{
    const TEST_WEBHOOK = '{
        "event": {
            "type": "test",
            "data": {
                "quote": {"content": "It works!"}
            }
        }
    }';

    const BILL_CREATED_WEBHOOK = '{
        "event":{
            "type":"bill_created",
            "data":{
                "bill":{"id":123456,"code":12345,"amount":"123.0","installments":1,"status":"pending","seen_at":null,"billing_at":null,"url":"https://sandbox-app.vindi.com.br/customer/bills/123456?token=abcde",
                "bill_items":[{"id":1234,"amount":"123.0","quantity":null,"product":{"id":1234567,"name":"Teste Vindi","code":"vindi_product_01"},"discount":null}],
                "charges":[{"id":870375,"amount":"123.0","status":"pending","due_at":"2019-07-23T23:59:59.000-03:00","paid_at":null,"installments":1,"attempt_count":1,"next_attempt":"2019-07-26T00:00:00.000-03:00","print_url":null,
                "last_transaction":{"id":1017664,"transaction_type":"authorization","status":"rejected","amount":"123.0","installments":1,"gateway_message":"Transacao rejeitada","gateway_response_code":"51","gateway_authorization":"","gateway_transaction_id":"abcdef","gateway_response_fields":{"nsu":"abcdef12345"},"gateway":{"id":1,"connector":"test"},
                "payment_profile":{"id":125445,"holder_name":"TESTES VINDI","registry_code":null,"card_expiration":"2020-01-31T23:59:59.000-02:00","card_number_first_six":"555555","card_number_last_four":"5557","payment_company":{"id":1,"name":"MasterCard","code":"mastercard"}}},
                "payment_method":{"id":1,"name":"Cartão de crédito","code":"credit_card","type":"PaymentMethod::CreditCard"}}],
                "customer":{"id":1,"name":"Vindi Tests","email":"test@vindi.com.br","code":"mag-1"},
                "period":{"id":1, "cycle": 2},
                "subscription":{"id":1},
                "payment_profile":null,"payment_condition":null}
            }
        }
    }';

    const BILL_PAID_WEBHOOK = '{
        "event":{
            "type":"bill_paid",
            "data":{
                "bill":{"id":123456,"code":12345,"amount":"123.0","installments":1,"status":"paid","seen_at":null,"billing_at":null,"url":"https://sandbox-app.vindi.com.br/customer/bills/123456?token=abcde",
                "bill_items":[{"id":1234,"amount":"123.0","quantity":null,"product":{"id":1234567,"name":"Teste Vindi","code":"vindi_product_01"},"discount":null}],
                "charges":[{"id":870375,"amount":"123.0","status":"paid","due_at":"2019-07-23T23:59:59.000-03:00","paid_at":null,"installments":1,"attempt_count":1,"next_attempt":null,"print_url":null,
                "last_transaction":{"id":1017664,"transaction_type":"authorization","status":"success","amount":"123.0","installments":1,"gateway_message":"Transacao autorizada","gateway_response_code":"51","gateway_authorization":"","gateway_transaction_id":"abcdef","gateway_response_fields":{"nsu":"abcdef12345"},"gateway":{"id":1,"connector":"test"},
                "payment_profile":{"id":125445,"holder_name":"TESTES VINDI","registry_code":null,"card_expiration":"2020-01-31T23:59:59.000-02:00","card_number_first_six":"555555","card_number_last_four":"5557","payment_company":{"id":1,"name":"MasterCard","code":"mastercard"}}},
                "payment_method":{"id":1,"name":"Cartão de crédito","code":"credit_card","type":"PaymentMethod::CreditCard"}}],
                "customer":{"id":1,"name":"Vindi Tests","email":"test@vindi.com.br","code":"mag-1"},
                "period":{"id":1, "cycle": 2},
                "subscription":{"id":1},
                "payment_profile":null,"payment_condition":null}
            }
        }
    }';

    const CHARGE_REJECTED_WEBHOOK = '{
        "event":{
            "type":"charge_rejected",
            "data":{
                "charge":{"id":870375,"amount":"123.0","status":"pending","due_at":"2019-07-23T23:59:59.000-03:00","paid_at":null,"installments":1,"attempt_count":1,"next_attempt":null,"print_url":null,
                "last_transaction":{"id":1017664,"transaction_type":"authorization","status":"rejected","amount":"123.0","installments":1,"gateway_message":"Transacao rejeitada","gateway_response_code":"57","gateway_authorization":"","gateway_transaction_id":"abcdef","gateway_response_fields":{"nsu":"abcdef12345"},"gateway":{"id":1,"connector":"test"},
                "payment_profile":{"id":125445,"holder_name":"TESTES VINDI","registry_code":null,"card_expiration":"2020-01-31T23:59:59.000-02:00","card_number_first_six":"555555","card_number_last_four":"5557","payment_company":{"id":1,"name":"MasterCard","code":"mastercard"}}},
                "bill":{"id":123456, "code":12345},
                "payment_method":{"id":1,"name":"Cartão de crédito","code":"credit_card","type":"PaymentMethod::CreditCard"}},
                "customer":{"id":1,"name":"Vindi Tests","email":"test@vindi.com.br","code":"mag-1"},"period":null,"subscription":null,"payment_profile":null,"payment_condition":null
            }
        }
    }';

    const SINGLE_BILL_CREATED_WEBHOOK = '{
        "event":{
            "type":"bill_created",
            "data":{
                "bill":{"id":123456,"code":12345,"amount":"123.0","installments":1,"status":"pending","seen_at":null,"billing_at":null,"url":"https://sandbox-app.vindi.com.br/customer/bills/123456?token=abcde",
                "bill_items":[{"id":1234,"amount":"123.0","quantity":null,"product":{"id":1234567,"name":"Teste Vindi","code":"vindi_product_01"},"discount":null}],
                "charges":[{"id":870375,"amount":"123.0","status":"pending","due_at":"2019-07-23T23:59:59.000-03:00","paid_at":null,"installments":1,"attempt_count":1,"next_attempt":"2019-07-26T00:00:00.000-03:00","print_url":null,
                "last_transaction":{"id":1017664,"transaction_type":"authorization","status":"rejected","amount":"123.0","installments":1,"gateway_message":"Transacao rejeitada","gateway_response_code":"51","gateway_authorization":"","gateway_transaction_id":"abcdef","gateway_response_fields":{"nsu":"abcdef12345"},"gateway":{"id":1,"connector":"test"},
                "payment_profile":{"id":125445,"holder_name":"TESTES VINDI","registry_code":null,"card_expiration":"2020-01-31T23:59:59.000-02:00","card_number_first_six":"555555","card_number_last_four":"5557","payment_company":{"id":1,"name":"MasterCard","code":"mastercard"}}},
                "payment_method":{"id":1,"name":"Cartão de crédito","code":"credit_card","type":"PaymentMethod::CreditCard"}}],
                "customer":{"id":1,"name":"Vindi Tests","email":"test@vindi.com.br","code":"mag-1"},
                "payment_profile":null,"payment_condition":null}
            }
        }
    }';

    const SINGLE_BILL_PAID_WEBHOOK = '{
        "event":{
            "type":"bill_paid",
            "data":{
                "bill":{"id":123456,"code":12345,"amount":"123.0","installments":1,"status":"paid","seen_at":null,"billing_at":null,"url":"https://sandbox-app.vindi.com.br/customer/bills/123456?token=abcde",
                "bill_items":[{"id":1234,"amount":"123.0","quantity":null,"product":{"id":1234567,"name":"Teste Vindi","code":"vindi_product_01"},"discount":null}],
                "charges":[{"id":870375,"amount":"123.0","status":"paid","due_at":"2019-07-23T23:59:59.000-03:00","paid_at":null,"installments":1,"attempt_count":1,"next_attempt":null,"print_url":null,
                "last_transaction":{"id":1017664,"transaction_type":"authorization","status":"success","amount":"123.0","installments":1,"gateway_message":"Transacao autorizada","gateway_response_code":"51","gateway_authorization":"","gateway_transaction_id":"abcdef","gateway_response_fields":{"nsu":"abcdef12345"},"gateway":{"id":1,"connector":"test"},
                "payment_profile":{"id":125445,"holder_name":"TESTES VINDI","registry_code":null,"card_expiration":"2020-01-31T23:59:59.000-02:00","card_number_first_six":"555555","card_number_last_four":"5557","payment_company":{"id":1,"name":"MasterCard","code":"mastercard"}}},
                "payment_method":{"id":1,"name":"Cartão de crédito","code":"credit_card","type":"PaymentMethod::CreditCard"}}],
                "customer":{"id":1,"name":"Vindi Tests","email":"test@vindi.com.br","code":"mag-1"},"period":null,"subscription":null,"payment_profile":null,"payment_condition":null}
            }
        }
    }';

    const FIRST_PERIOD_BILL_CREATED_WEBHOOK = '{
        "event":{
            "type":"bill_created",
            "data":{
                "bill":{"id":123456,"code":12345,"amount":"123.0","installments":1,"status":"pending","seen_at":null,"billing_at":null,"url":"https://sandbox-app.vindi.com.br/customer/bills/123456?token=abcde",
                "bill_items":[{"id":1234,"amount":"123.0","quantity":null,"product":{"id":1234567,"name":"Teste Vindi","code":"vindi_product_01"},"discount":null}],
                "charges":[{"id":870375,"amount":"123.0","status":"pending","due_at":"2019-07-23T23:59:59.000-03:00","paid_at":null,"installments":1,"attempt_count":1,"next_attempt":"2019-07-26T00:00:00.000-03:00","print_url":null,
                "last_transaction":{"id":1017664,"transaction_type":"authorization","status":"rejected","amount":"123.0","installments":1,"gateway_message":"Transacao rejeitada","gateway_response_code":"51","gateway_authorization":"","gateway_transaction_id":"abcdef","gateway_response_fields":{"nsu":"abcdef12345"},"gateway":{"id":1,"connector":"test"},
                "payment_profile":{"id":125445,"holder_name":"TESTES VINDI","registry_code":null,"card_expiration":"2020-01-31T23:59:59.000-02:00","card_number_first_six":"555555","card_number_last_four":"5557","payment_company":{"id":1,"name":"MasterCard","code":"mastercard"}}},
                "payment_method":{"id":1,"name":"Cartão de crédito","code":"credit_card","type":"PaymentMethod::CreditCard"}}],
                "customer":{"id":1,"name":"Vindi Tests","email":"test@vindi.com.br","code":"mag-1"},
                "period":{"id":1, "cycle": 1},
                "subscription":{"id":1},
                "payment_profile":null,"payment_condition":null}
            }
        }
    }';

    const INVALID_BILL_CREATED_WEBHOOK = '{
        "event":{
            "type":"bill_created",
            "data":{
                "bill":{"id":123456,"code":12345,"amount":"123.0","installments":1,"status":"pending","seen_at":null,"billing_at":null,"url":"https://sandbox-app.vindi.com.br/customer/bills/123456?token=abcde",
                "bill_items":[{"id":1234,"amount":"123.0","quantity":null,"product":{"id":1234567,"name":"Teste Vindi","code":"vindi_product_01"},"discount":null}],
                "charges":[{"id":870375,"amount":"123.0","status":"pending","due_at":"2019-07-23T23:59:59.000-03:00","paid_at":null,"installments":1,"attempt_count":1,"next_attempt":"2019-07-26T00:00:00.000-03:00","print_url":null,
                "last_transaction":{"id":1017664,"transaction_type":"authorization","status":"rejected","amount":"123.0","installments":1,"gateway_message":"Transacao rejeitada","gateway_response_code":"51","gateway_authorization":"","gateway_transaction_id":"abcdef","gateway_response_fields":{"nsu":"abcdef12345"},"gateway":{"id":1,"connector":"test"},
                "payment_profile":{"id":125445,"holder_name":"TESTES VINDI","registry_code":null,"card_expiration":"2020-01-31T23:59:59.000-02:00","card_number_first_six":"555555","card_number_last_four":"5557","payment_company":{"id":1,"name":"MasterCard","code":"mastercard"}}},
                "payment_method":{"id":1,"name":"Cartão de crédito","code":"credit_card","type":"PaymentMethod::CreditCard"}}],
                "customer":{"id":1,"name":"Vindi Tests","email":"test@vindi.com.br","code":"mag-1"},
                "period":{"id":1},
                "subscription":{},
                "payment_profile":null,"payment_condition":null}
            }
        }
    }';
}