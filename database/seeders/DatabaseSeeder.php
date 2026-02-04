<?php

namespace Database\Seeders;

use Carbon\Carbon;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /*$user = User::create([
            'name' => 'Miguel Quesada',
            'email' => 'm.quesada@callmedicall.com',
            'password' => Hash::make('Quesmi01'),
            'active' => 1,
            'is_admin' => 1,
            'created_at' => Carbon::now()->format(app('global_format_datetime_milisecond')),
        ]);
        $user = User::create([
            'name' => 'Api User',
            'email' => 'api.user@callmedicall.com',
            'password' => Hash::make('Quesmi01'),
            'active' => 1,
            'is_admin' => 1,
            'created_at' => Carbon::now()->format(app('global_format_datetime_milisecond')),
        ]);*/
        /*$user = User::create([
            'name' => 'Gregory naze',
            'email' => 'naze@callmedicall.com',
            'password' => Hash::make('Gregna$01'),
            'active' => 1,
            'is_admin' => 1,
            'created_at' => Carbon::now()->format(app('global_format_datetime_milisecond')),
        ]);*/
        $user = User::create([
            'name' => 'Serge ABEND',
            'email' => 'sergea@balink.net',
            'password' => Hash::make('8$7wiP<q9M<L'),
            'active' => 1,
            'is_admin' => 1,
            'user_type' => 'Admin',
            'created_at' => Carbon::now()->format(app('global_format_datetime_milisecond')),
        ]);
    }
}
