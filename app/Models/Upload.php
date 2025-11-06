<?php

namespace App\Models;

use App\Enums\UploadStatus;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    protected $fillable = [
        'file_name',
        'status',
        'processed_at',
    ];

    protected $casts = [
        'status' => UploadStatus::class,
    ];
}
