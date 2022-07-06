<?php

namespace ZnUser\Registration\Domain\Enums\Rbac;

use ZnCore\Enum\Interfaces\GetLabelsInterface;

class UserRegistrationPermissionEnum implements GetLabelsInterface
{

    const REQUEST_ACTIVATION_CODE = 'oRegistrationRequestActivationCode';
    const CREATE_ACCOUNT = 'oRegistrationCreateAccount';

    public static function getLabels()
    {
        return [
            self::REQUEST_ACTIVATION_CODE => 'Запросить код активации',
            self::CREATE_ACCOUNT => 'Создать аккаунт',
        ];
    }
}