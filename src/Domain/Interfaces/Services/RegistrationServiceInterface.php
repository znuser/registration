<?php

namespace ZnUser\Registration\Domain\Interfaces\Services;

use ZnUser\Registration\Domain\Forms\RegistrationForm;
use ZnUser\Registration\Domain\Forms\RequestActivationCodeForm;

interface RegistrationServiceInterface
{

    public function requestActivationCode(RequestActivationCodeForm $requestActivationCodeForm);

    //public function createAccount(RegistrationForm $registrationForm, string $activationCode);
}
