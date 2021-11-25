<?php

namespace App\Controllers\Juno;

class Balance extends Resource {

    public function endpoint(): string
    {
        return 'balance';
    }

    public function retrieveBalance()
    {
        return $this->all();
    }
}