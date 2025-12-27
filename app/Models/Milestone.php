<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
     use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'due_date',
        'status',
        'completed_at',
        'order',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function complete(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'pending')
            ->where('due_date', '>=', now());
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }         
}
