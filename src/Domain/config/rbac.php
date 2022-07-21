<?php

use ZnUser\Rbac\Domain\Enums\Rbac\SystemRoleEnum;
use ZnUser\Registration\Domain\Enums\Rbac\UserRegistrationPermissionEnum;

return [
    'roleEnums' => [
        SystemRoleEnum::class,
    ],
    'permissionEnums' => [
        UserRegistrationPermissionEnum::class,
    ],
    'inheritance' => [
        SystemRoleEnum::GUEST => [
            UserRegistrationPermissionEnum::REQUEST_ACTIVATION_CODE,
            UserRegistrationPermissionEnum::CREATE_ACCOUNT,
        ],
    ],
];
