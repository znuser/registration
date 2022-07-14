<?php

namespace ZnUser\Registration\Domain;

use ZnDomain\Domain\Interfaces\DomainInterface;

class Domain implements DomainInterface
{

    public function getName()
    {
        return 'userRegistration';
    }
}
