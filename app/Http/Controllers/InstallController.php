<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\InstallHelper;

class InstallController extends Controller
{
    public function install(Request $request)
    {
        //InstallHelper::createInitialFolders();
        InstallHelper::chnageTimeStampsType();
        echo "install effectué avec succes\n";
    }
}
