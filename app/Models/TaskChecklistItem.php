<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskChecklistItem extends Model
{
    protected $fillable = [
        'task_id',
        'title',
        'is_completed',
        'completed_by',
        'completed_at',
        'order',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function markAsCompleted(User $user): void
    {
        $this->update([
            'is_completed' => true,
            'completed_by' => $user->id,
            'completed_at' => now(),
        ]);
    }

    public function markAsIncomplete(): void
    {
        $this->update([
            'is_completed' => false,
            'completed_by' => null,
            'completed_at' => null,
        ]);
    }
}
