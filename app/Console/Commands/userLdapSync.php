<?php

namespace App\Console\Commands;

use App\Helpers\NomaneHelper;
use Illuminate\Console\Command;

class userLdapSync extends Command
{
    protected $signature = 'user:LdapSync';
    protected $description = 'Ldap Sync';
    public function handle() {
        NomaneHelper::doApiCommandCall('/ldap-sync', $this->description);
    }
}
