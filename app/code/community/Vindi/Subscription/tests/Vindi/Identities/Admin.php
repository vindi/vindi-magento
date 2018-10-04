<?php

namespace Vindi\Identities;


class Admin extends AbstractEntity
{

    const IDENTITY = 'Admin';

    public $account   = 'admin';
    public $password  = 'password123';

    public function getAccount()
    {
        return $this->account;
    }

    public function setAccount($account)
    {
        $this->account = $account;
    }
    
}