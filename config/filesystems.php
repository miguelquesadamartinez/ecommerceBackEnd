<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'ssh' => [
            'driver' => 'local',
            'root'   => storage_path('app'),
        ],

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        # ToDO: Upgrade paths to sFtp server

        'nomane_ftp_out_folders' => [
            'driver' => 'local',
            'root'   => storage_path('app/private/noName/out'),
        ],

        /*
        'nomane_ftp_out_folders' => [
            'driver' => 'sftp',
            'host' => env('NONAME_FTP_HOST'),
            'username' => env('NONAME_FTP_USER'),
            //'password' =>  env('NONAME_FTP_PASS'),
            'privateKey' => env('SFTP_PRIVATE_KEY'),
            //'passphrase' => env('NONAME_FTP_PASS'),
            'port' => 2222,
            'root' => env('SFTP_ROOT', '/'),
            'timeout' => 30,
            'directoryPerm' => 0755
        ],
        */
/*
        'cagedim_ftp_in_folders' => [
            'driver' => 'sftp',
            'host' => env('CAGEDIM_FTP_HOST'),
            'username' => env('CAGEDIM_FTP_USER'),
            'password' =>  env('CAGEDIM_FTP_PASS'),
            'port' => 22,
            'root' => env('CAGEDIM_SFTP_ROOT', '/'),
            'timeout' => 30,
        ],
*/
        'cagedim_ftp_in_folders' => [
            'driver' => 'local',
            'root'   => storage_path('app/private/noName/in'),
        ],

        'nomane_ftp_in_folders' => [
            'driver' => 'local',
            'root'   => storage_path('app/private/noName/in'),
        ],

        'nomane_temp_folder' => [
            'driver' => 'local',
            'root'   => storage_path('app/private/noName/temp'),
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];


