<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    protected $fillable = [
        'invitable_type',
        'invitable_id',
        'inviter_id',
        'invitee_email',
        'invitee_id',
        'role',
        'token',
        'status',
        'expires_at',
        'accepted_at',
        'rejected_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function invitable()
    {
        return $this->morphTo();
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function invitee()
    {
        return $this->belongsTo(User::class, 'invitee_id');
    }

    public function accept(User $user): void
    {
        $this->update([
            'status' => 'accepted',
            'invitee_id' => $user->id,
            'accepted_at' => now(),
        ]);
    }

    public function reject(): void
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending')
            ->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'pending')
            ->where('expires_at', '<=', now());
    }
}
