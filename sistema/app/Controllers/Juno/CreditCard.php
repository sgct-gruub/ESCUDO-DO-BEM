<?php

namespace App\Controllers\Juno;

class CreditCard extends Resource {

    public function endpoint(): string
    {
        return 'credit-cards/tokenization';
    }

    public function tokenizeCard(array $form_params = [])
    {
        return $this->create($form_params);
    }
}
