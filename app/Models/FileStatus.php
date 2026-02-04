<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileStatus extends Model
{
    protected $table = 'file_statuses';
    protected $fillable = [
        'file_status_filename',
        'file_status_status',
        'file_status_source',
        'file_status_type',
        'file_status_error_message',
        'file_status_error_code',
        'file_status_error_line',
    ];
}
