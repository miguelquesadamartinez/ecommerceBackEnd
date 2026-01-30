<?php

namespace App\Console\Commands;

use App\Helpers\PfizerHelper;
use Illuminate\Console\Command;

class userLdapSync extends Command
{
    protected $signature = 'user:LdapSync';
    protected $description = 'Ldap Sync';
    public function handle() {
        PfizerHelper::doApiCommandCall('/ldap-sync', $this->description);
    }
}
