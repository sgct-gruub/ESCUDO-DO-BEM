<?php

namespace App\Controllers\Juno;

use App\Controllers\Juno\Resource;

class NewOnboarding extends Resource {

    public function endpoint(): string
    {
        return 'onboarding/link-request';
    }

    public function createOnboardingWhiteLabel(array $form_params = [])
    {
        return $this->create($form_params);
    }
}