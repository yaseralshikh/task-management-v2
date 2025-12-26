<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    /** @use HasFactory<\Database\Factories\CommentFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'commentable_type',
        'commentable_id',
        'user_id',
        'parent_id',
        'content',
        'is_edited',
        'edited_at',
    ];

    protected $casts = [
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
    ];

    public function commentable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function markAsEdited(): void
    {
        $this->update([
            'is_edited' => true,
            'edited_at' => now(),
        ]);
    }    
}
