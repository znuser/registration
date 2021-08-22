<?php

namespace ZnUser\Registration\Domain;

use ZnCore\Domain\Interfaces\DomainInterface;

class Domain implements DomainInterface
{

    public function getName()
    {
        return 'userRegistration';
    }
}
