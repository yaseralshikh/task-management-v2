<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Project Resource - Updated
 * 
 * بدون budget و currency
 */
class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'color' => $this->color,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'progress_percentage' => $this->progress_percentage,
            'is_archived' => $this->is_archived,
            'is_active' => $this->is_active,
            'is_completed' => $this->is_completed,
            
            // التواريخ
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'archived_at' => $this->archived_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            
            // العلاقات
            'team' => new TeamResource($this->whenLoaded('team')),
            'owner' => new UserResource($this->whenLoaded('owner')),
            'archived_by_user' => new UserResource($this->whenLoaded('archivedBy')),
            'members' => UserResource::collection($this->whenLoaded('members')),
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'milestones' => MilestoneResource::collection($this->whenLoaded('milestones')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),
            
            // الإحصائيات (إذا تم طلبها)
            'stats' => $this->when($request->has('include_stats'), function () {
                return [
                    'total_tasks' => $this->total_tasks,
                    'completed_tasks' => $this->completed_tasks,
                    'in_progress_tasks' => $this->tasks()->where('status', 'in_progress')->count(),
                    'todo_tasks' => $this->tasks()->where('status', 'todo')->count(),
                    'overdue_tasks' => $this->getOverdueTasks()->count(),
                    'total_members' => $this->members()->count(),
                ];
            }),
        ];
    }

    /**
     * تسميات الحالات بالعربية
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'planning' => 'قيد التخطيط',
            'active' => 'نشط',
            'on_hold' => 'معلق',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
            default => $this->status,
        };
    }
}
