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
    const ACTIVE_ONLINE_BANK_SLIP = array(
        'payment_methods' => array(
            array(
                'name'        => 'Boleto bancário Online',
                'type'        => 'PaymentMethod::OnlineBankSlip',
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
}
