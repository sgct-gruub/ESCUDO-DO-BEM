<?php

namespace App\Controllers\Juno;

use App\Controllers\Juno\Resource;

class Pix extends Resource {

    public function endpoint(): string
    {
        return 'pix';
    }

    public function createRandomKey($id = null, $action = null, array $form_params = [])
    {
        return $this->post(id,'keys', $form_params);
    }

    public function createStaticQRCode($id = null, $action = null, array $form_params = [])
    {
        return $this->post(id,'qrcodes/static', $form_params);
    }


}