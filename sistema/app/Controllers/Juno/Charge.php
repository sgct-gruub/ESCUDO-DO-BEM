<?php

namespace App\Controllers\Juno;

class Charge extends Resource {

    public function endpoint(): string
    {
        return 'charges';
    }

    public function createCharge(array $form_params = [])
    {
        return $this->create($form_params);
    }

    public function getCharge($id)
    {
        return $this->retrieve($id);
    }

    public function getCharges()
    {
        return $this->all();
    }
}
