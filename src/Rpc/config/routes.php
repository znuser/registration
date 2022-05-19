<?php

use ZnUser\Registration\Domain\Enums\Rbac\UserRegistrationPermissionEnum;
use ZnUser\Registration\Rpc\Controllers\RegistrationController;

return [
    [
        'method_name' => 'userRegistration.requestActivationCode',
        'version' => '1',
        'is_verify_eds' => false,
        'is_verify_auth' => false,
        'permission_name' => UserRegistrationPermissionEnum::REQUEST_ACTIVATION_CODE,
        'handler_class' => RegistrationController::class,
        'handler_method' => 'requestActivationCode',
        'status_id' => 100,
        'title' => null,
        'description' => null,
    ],
    [
        'method_name' => 'userRegistration.createAccount',
        'version' => '1',
        'is_verify_eds' => false,
        'is_verify_auth' => false,
        'permission_name' => UserRegistrationPermissionEnum::CREATE_ACCOUNT,
        'handler_class' => RegistrationController::class,
        'handler_method' => 'createAccount',
        'status_id' => 100,
        'title' => null,
        'description' => null,
    ],
];