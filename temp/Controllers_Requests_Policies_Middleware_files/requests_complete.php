<?php

/**
 * ============================================================================
 * Laravel 12 Form Requests - نظام إدارة المهام
 * ============================================================================
 */

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

// ============================================================================
// Auth Requests
// ============================================================================
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'national_id' => 'required|string|size:10|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)],
            'phone' => 'nullable|string|max:20',
            'job_title' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'الاسم مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل',
            'national_id.required' => 'رقم الهوية الوطنية مطلوب',
            'national_id.size' => 'رقم الهوية يجب أن يكون 10 أرقام',
            'national_id.unique' => 'رقم الهوية مستخدم بالفعل',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.confirmed' => 'كلمة المرور غير متطابقة',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
        ];
    }
}

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'password.required' => 'كلمة المرور مطلوبة',
        ];
    }
}

// ============================================================================
// Team Requests
// ============================================================================
namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:teams,name',
            'slug' => 'sometimes|string|max:255|unique:teams,slug',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'color' => 'nullable|string|regex:/^#([A-Fa-f0-9]{6})$/',
            'max_members' => 'nullable|integer|min:1',
            'settings' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم الفريق مطلوب',
            'name.unique' => 'اسم الفريق مستخدم بالفعل',
            'name.max' => 'اسم الفريق يجب ألا يتجاوز 255 حرف',
            'slug.unique' => 'المعرف مستخدم بالفعل',
            'logo.image' => 'يجب أن يكون الملف صورة',
            'logo.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميجابايت',
            'color.regex' => 'اللون يجب أن يكون بصيغة Hex صحيحة',
            'max_members.min' => 'الحد الأقصى للأعضاء يجب أن يكون 1 على الأقل',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('slug') && $this->has('name')) {
            $this->merge([
                'slug' => Str::slug($this->name),
            ]);
        }
    }
}

class UpdateTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('team'));
    }

    public function rules(): array
    {
        $teamId = $this->route('team')->id;

        return [
            'name' => 'sometimes|string|max:255|unique:teams,name,' . $teamId,
            'slug' => 'sometimes|string|max:255|unique:teams,slug,' . $teamId,
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'color' => 'nullable|string|regex:/^#([A-Fa-f0-9]{6})$/',
            'is_active' => 'sometimes|boolean',
            'max_members' => 'nullable|integer|min:1',
            'settings' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'اسم الفريق مستخدم بالفعل',
            'slug.unique' => 'المعرف مستخدم بالفعل',
            'logo.image' => 'يجب أن يكون الملف صورة',
            'color.regex' => 'اللون يجب أن يكون بصيغة Hex صحيحة',
        ];
    }
}

// ============================================================================
// Project Requests
// ============================================================================
namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:projects,slug',
            'description' => 'nullable|string',
            'color' => 'nullable|string|regex:/^#([A-Fa-f0-9]{6})$/',
            'team_id' => 'nullable|exists:teams,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:planning,active,on_hold,completed,cancelled',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم المشروع مطلوب',
            'name.max' => 'اسم المشروع يجب ألا يتجاوز 255 حرف',
            'slug.unique' => 'المعرف مستخدم بالفعل',
            'color.regex' => 'اللون يجب أن يكون بصيغة Hex صحيحة',
            'team_id.exists' => 'الفريق المحدد غير موجود',
            'end_date.after_or_equal' => 'تاريخ الانتهاء يجب أن يكون بعد أو يساوي تاريخ البداية',
            'status.in' => 'الحالة المحددة غير صحيحة',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('slug') && $this->has('name')) {
            $this->merge([
                'slug' => Str::slug($this->name),
            ]);
        }
    }
}

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
    }

    public function rules(): array
    {
        $projectId = $this->route('project')->id;

        return [
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:projects,slug,' . $projectId,
            'description' => 'nullable|string',
            'color' => 'nullable|string|regex:/^#([A-Fa-f0-9]{6})$/',
            'team_id' => 'nullable|exists:teams,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'sometimes|in:planning,active,on_hold,completed,cancelled',
            'progress_percentage' => 'sometimes|numeric|min:0|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'اسم المشروع يجب ألا يتجاوز 255 حرف',
            'slug.unique' => 'المعرف مستخدم بالفعل',
            'color.regex' => 'اللون يجب أن يكون بصيغة Hex صحيحة',
            'team_id.exists' => 'الفريق المحدد غير موجود',
            'end_date.after_or_equal' => 'تاريخ الانتهاء يجب أن يكون بعد أو يساوي تاريخ البداية',
            'status.in' => 'الحالة المحددة غير صحيحة',
            'progress_percentage.min' => 'نسبة التقدم يجب أن تكون 0 على الأقل',
            'progress_percentage.max' => 'نسبة التقدم يجب ألا تتجاوز 100',
        ];
    }
}

// ============================================================================
// Task Requests
// ============================================================================
namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:todo,in_progress,done',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'parent_task_id' => 'nullable|exists:tasks,id',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'order' => 'nullable|integer|min:0',
            'assigned_user_ids' => 'nullable|array',
            'assigned_user_ids.*' => 'exists:users,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
            'checklist_items' => 'nullable|array',
            'checklist_items.*.title' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'عنوان المهمة مطلوب',
            'title.max' => 'عنوان المهمة يجب ألا يتجاوز 255 حرف',
            'status.in' => 'الحالة المحددة غير صحيحة',
            'priority.in' => 'الأولوية المحددة غير صحيحة',
            'assigned_to.exists' => 'المستخدم المعين غير موجود',
            'parent_task_id.exists' => 'المهمة الأب غير موجودة',
            'due_date.after_or_equal' => 'تاريخ الاستحقاق يجب أن يكون بعد أو يساوي تاريخ البداية',
            'estimated_hours.min' => 'الساعات المقدرة يجب أن تكون 0 على الأقل',
            'assigned_user_ids.*.exists' => 'أحد المستخدمين المعينين غير موجود',
            'tag_ids.*.exists' => 'أحد الوسوم المحددة غير موجود',
            'checklist_items.*.title.required' => 'عنوان العنصر مطلوب',
        ];
    }
}

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('task'));
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:todo,in_progress,done',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
            'progress_percentage' => 'nullable|numeric|min:0|max:100',
            'blocking_reason' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'assigned_user_ids' => 'nullable|array',
            'assigned_user_ids.*' => 'exists:users,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'عنوان المهمة يجب ألا يتجاوز 255 حرف',
            'status.in' => 'الحالة المحددة غير صحيحة',
            'priority.in' => 'الأولوية المحددة غير صحيحة',
            'assigned_to.exists' => 'المستخدم المعين غير موجود',
            'due_date.after_or_equal' => 'تاريخ الاستحقاق يجب أن يكون بعد أو يساوي تاريخ البداية',
            'estimated_hours.min' => 'الساعات المقدرة يجب أن تكون 0 على الأقل',
            'actual_hours.min' => 'الساعات الفعلية يجب أن تكون 0 على الأقل',
            'progress_percentage.min' => 'نسبة التقدم يجب أن تكون 0 على الأقل',
            'progress_percentage.max' => 'نسبة التقدم يجب ألا تتجاوز 100',
        ];
    }
}

// ============================================================================
// Comment Requests
// ============================================================================
namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:comments,id',
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'محتوى التعليق مطلوب',
            'parent_id.exists' => 'التعليق الأب غير موجود',
        ];
    }
}

class UpdateCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('comment'));
    }

    public function rules(): array
    {
        return [
            'content' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'محتوى التعليق مطلوب',
        ];
    }
}

// ============================================================================
// TimeEntry Requests
// ============================================================================
namespace App\Http\Requests\TimeEntry;

use Illuminate\Foundation\Http\FormRequest;

class StoreTimeEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => 'nullable|string',
            'started_at' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'started_at.date' => 'تاريخ البداية غير صحيح',
        ];
    }
}

// ============================================================================
// Tag Requests
// ============================================================================
namespace App\Http\Requests\Tag;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:50|unique:tags,name',
            'slug' => 'sometimes|string|max:50|unique:tags,slug',
            'color' => 'nullable|string|regex:/^#([A-Fa-f0-9]{6})$/',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم الوسم مطلوب',
            'name.max' => 'اسم الوسم يجب ألا يتجاوز 50 حرف',
            'name.unique' => 'اسم الوسم مستخدم بالفعل',
            'slug.unique' => 'المعرف مستخدم بالفعل',
            'color.regex' => 'اللون يجب أن يكون بصيغة Hex صحيحة',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('slug') && $this->has('name')) {
            $this->merge([
                'slug' => Str::slug($this->name),
            ]);
        }

        if (!$this->has('color')) {
            $this->merge([
                'color' => '#' . substr(md5($this->name), 0, 6),
            ]);
        }
    }
}

class UpdateTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('tag'));
    }

    public function rules(): array
    {
        $tagId = $this->route('tag')->id;

        return [
            'name' => 'sometimes|string|max:50|unique:tags,name,' . $tagId,
            'slug' => 'sometimes|string|max:50|unique:tags,slug,' . $tagId,
            'color' => 'nullable|string|regex:/^#([A-Fa-f0-9]{6})$/',
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'اسم الوسم يجب ألا يتجاوز 50 حرف',
            'name.unique' => 'اسم الوسم مستخدم بالفعل',
            'slug.unique' => 'المعرف مستخدم بالفعل',
            'color.regex' => 'اللون يجب أن يكون بصيغة Hex صحيحة',
        ];
    }
}

// ============================================================================
// Milestone Requests
// ============================================================================
namespace App\Http\Requests\Milestone;

use Illuminate\Foundation\Http\FormRequest;

class StoreMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'order' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم المعلم الرئيسي مطلوب',
            'name.max' => 'الاسم يجب ألا يتجاوز 255 حرف',
            'due_date.required' => 'تاريخ الاستحقاق مطلوب',
            'due_date.date' => 'تاريخ الاستحقاق غير صحيح',
        ];
    }
}

class UpdateMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'sometimes|date',
            'status' => 'sometimes|in:pending,completed,missed',
            'order' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'الاسم يجب ألا يتجاوز 255 حرف',
            'due_date.date' => 'تاريخ الاستحقاق غير صحيح',
            'status.in' => 'الحالة المحددة غير صحيحة',
        ];
    }
}

// ============================================================================
// TaskChecklistItem Requests
// ============================================================================
namespace App\Http\Requests\TaskChecklistItem;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskChecklistItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'order' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'عنوان العنصر مطلوب',
            'title.max' => 'العنوان يجب ألا يتجاوز 255 حرف',
        ];
    }
}

class UpdateTaskChecklistItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'is_completed' => 'sometimes|boolean',
            'order' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'العنوان يجب ألا يتجاوز 255 حرف',
        ];
    }
}
