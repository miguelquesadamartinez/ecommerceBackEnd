<?php

namespace App\Ldap;

use LdapRecord\Models\ActiveDirectory\User as BaseUser;

class LdapUser extends BaseUser
{
    public static $objectClasses = [
        'top',
        'person',
        'organizationalPerson',
        'user',
    ];
}
