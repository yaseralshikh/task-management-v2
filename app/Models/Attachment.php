<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attachment extends Model
{
    /** @use HasFactory<\Database\Factories\AttachmentFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'user_id',
        'file_name',
        'file_original_name',
        'file_path',
        'file_size',
        'file_type',
        'file_extension',
        'is_image',
        'thumbnail_path',
    ];

    protected $casts = [
        'is_image' => 'boolean',
        'file_size' => 'integer',
    ];

    public function attachable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFileSizeInMbAttribute(): float
    {
        return round($this->file_size / 1024 / 1024, 2);
    }

    public function getFileUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }    
}
