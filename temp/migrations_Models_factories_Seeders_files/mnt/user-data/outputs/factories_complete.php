<?php

/**
 * ============================================================================
 * Laravel Factories - نظام إدارة المهام
 * ============================================================================
 */

namespace Database\Factories;

use App\Models\User;
use App\Models\Team;
use App\Models\Project;
use App\Models\Task;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Tag;
use App\Models\Comment;
use App\Models\Attachment;
use App\Models\TimeEntry;
use App\Models\TaskChecklistItem;
use App\Models\Milestone;
use App\Models\TaskDependency;
use App\Models\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

// ============================================================================
// UserFactory
// ============================================================================
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'national_id' => fake()->unique()->numerify('##########'),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'avatar' => fake()->imageUrl(200, 200, 'people'),
            'phone' => fake()->phoneNumber(),
            'job_title' => fake()->jobTitle(),
            'bio' => fake()->paragraph(),
            'timezone' => 'Asia/Riyadh',
            'language' => 'ar',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'week_starts_on' => 6,
            'theme' => fake()->randomElement(['light', 'dark', 'auto']),
            'is_owner' => false,
            'is_active' => true,
            'last_login_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'remember_token' => Str::random(10),
        ];
    }

    public function owner(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_owner' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}

// ============================================================================
// RoleFactory
// ============================================================================
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Super Admin',
            'Team Leader',
            'Project Manager',
            'Developer',
            'Designer',
            'Tester',
            'Client',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'is_system' => false,
        ];
    }

    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_system' => true,
        ]);
    }
}

// ============================================================================
// PermissionFactory
// ============================================================================
class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        $groups = ['teams', 'projects', 'tasks', 'users', 'reports'];
        $actions = ['view', 'create', 'edit', 'delete'];
        
        $group = fake()->randomElement($groups);
        $action = fake()->randomElement($actions);
        $name = ucfirst($action) . ' ' . ucfirst($group);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'group' => $group,
        ];
    }
}

// ============================================================================
// TeamFactory
// ============================================================================
class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'logo' => fake()->imageUrl(400, 400, 'business'),
            'color' => fake()->hexColor(),
            'is_active' => true,
            'max_members' => fake()->numberBetween(5, 50),
            'settings' => [
                'allow_guest' => fake()->boolean(),
                'auto_archive' => fake()->boolean(),
            ],
            'owner_id' => User::factory(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withOwner(User $owner): static
    {
        return $this->state(fn (array $attributes) => [
            'owner_id' => $owner->id,
        ]);
    }
}

// ============================================================================
// ProjectFactory
// ============================================================================
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $name = fake()->unique()->catchPhrase();
        $startDate = fake()->dateTimeBetween('-6 months', 'now');
        $endDate = fake()->dateTimeBetween($startDate, '+6 months');

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraphs(3, true),
            'color' => fake()->hexColor(),
            'team_id' => Team::factory(),
            'owner_id' => User::factory(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'progress_percentage' => fake()->randomFloat(2, 0, 100),
            'status' => fake()->randomElement(['planning', 'active', 'on_hold', 'completed', 'cancelled']),
            'is_archived' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'is_archived' => false,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'progress_percentage' => 100,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_archived' => true,
            'archived_at' => now(),
            'archived_by' => User::factory(),
        ]);
    }

    public function withTeam(Team $team): static
    {
        return $this->state(fn (array $attributes) => [
            'team_id' => $team->id,
        ]);
    }

    public function withOwner(User $owner): static
    {
        return $this->state(fn (array $attributes) => [
            'owner_id' => $owner->id,
        ]);
    }
}

// ============================================================================
// TaskFactory
// ============================================================================
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-2 months', '+1 month');
        $dueDate = fake()->dateTimeBetween($startDate, '+2 months');
        $status = fake()->randomElement(['todo', 'in_progress', 'done']);

        return [
            'project_id' => Project::factory(),
            'team_id' => null,
            'assigned_to' => User::factory(),
            'parent_task_id' => null,
            'title' => fake()->sentence(),
            'description' => fake()->paragraphs(2, true),
            'status' => $status,
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'start_date' => $startDate,
            'due_date' => $dueDate,
            'estimated_hours' => fake()->randomFloat(2, 1, 40),
            'actual_hours' => $status === 'done' ? fake()->randomFloat(2, 1, 40) : null,
            'progress_percentage' => $status === 'done' ? 100 : fake()->randomFloat(2, 0, 90),
            'completed_at' => $status === 'done' ? now() : null,
            'blocking_reason' => null,
            'is_recurring' => fake()->boolean(10),
            'recurrence_pattern' => null,
            'order' => fake()->numberBetween(0, 100),
            'created_by' => User::factory(),
        ];
    }

    public function todo(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'todo',
            'progress_percentage' => 0,
            'completed_at' => null,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'progress_percentage' => fake()->randomFloat(2, 10, 90),
            'completed_at' => null,
        ]);
    }

    public function done(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'done',
            'progress_percentage' => 100,
            'completed_at' => now(),
            'actual_hours' => fake()->randomFloat(2, 1, 40),
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => fake()->dateTimeBetween('-30 days', '-1 day'),
            'status' => 'todo',
        ]);
    }

    public function subtask(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_task_id' => Task::factory(),
        ]);
    }

    public function withProject(Project $project): static
    {
        return $this->state(fn (array $attributes) => [
            'project_id' => $project->id,
            'team_id' => $project->team_id,
        ]);
    }

    public function assignedTo(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => $user->id,
        ]);
    }

    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user->id,
        ]);
    }
}

// ============================================================================
// CommentFactory
// ============================================================================
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'commentable_type' => Task::class,
            'commentable_id' => Task::factory(),
            'user_id' => User::factory(),
            'parent_id' => null,
            'content' => fake()->paragraph(),
            'is_edited' => false,
            'edited_at' => null,
        ];
    }

    public function onTask(Task $task): static
    {
        return $this->state(fn (array $attributes) => [
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
        ]);
    }

    public function onProject(Project $project): static
    {
        return $this->state(fn (array $attributes) => [
            'commentable_type' => Project::class,
            'commentable_id' => $project->id,
        ]);
    }

    public function reply(Comment $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'commentable_type' => $parent->commentable_type,
            'commentable_id' => $parent->commentable_id,
        ]);
    }

    public function edited(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_edited' => true,
            'edited_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }
}

// ============================================================================
// AttachmentFactory
// ============================================================================
class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    public function definition(): array
    {
        $isImage = fake()->boolean(40);
        $fileName = fake()->uuid();
        $extension = $isImage ? fake()->randomElement(['jpg', 'png', 'gif']) : fake()->randomElement(['pdf', 'docx', 'xlsx']);

        return [
            'attachable_type' => Task::class,
            'attachable_id' => Task::factory(),
            'user_id' => User::factory(),
            'file_name' => $fileName . '.' . $extension,
            'file_original_name' => fake()->word() . '.' . $extension,
            'file_path' => 'attachments/' . $fileName . '.' . $extension,
            'file_size' => fake()->numberBetween(10000, 5000000),
            'file_type' => $isImage ? 'image/' . $extension : 'application/' . $extension,
            'file_extension' => $extension,
            'is_image' => $isImage,
            'thumbnail_path' => $isImage ? 'thumbnails/' . $fileName . '.jpg' : null,
        ];
    }

    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_extension' => 'jpg',
            'file_type' => 'image/jpeg',
            'is_image' => true,
            'thumbnail_path' => 'thumbnails/' . fake()->uuid() . '.jpg',
        ]);
    }

    public function pdf(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_extension' => 'pdf',
            'file_type' => 'application/pdf',
            'is_image' => false,
            'thumbnail_path' => null,
        ]);
    }
}

// ============================================================================
// TagFactory
// ============================================================================
class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'color' => fake()->hexColor(),
            'created_by' => User::factory(),
        ];
    }
}

// ============================================================================
// TimeEntryFactory
// ============================================================================
class TimeEntryFactory extends Factory
{
    protected $model = TimeEntry::class;

    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-30 days', 'now');
        $endedAt = fake()->dateTimeBetween($startedAt, '+8 hours');
        $durationMinutes = $endedAt->diffInMinutes($startedAt);

        return [
            'task_id' => Task::factory(),
            'user_id' => User::factory(),
            'description' => fake()->sentence(),
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'duration_minutes' => $durationMinutes,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'ended_at' => null,
            'duration_minutes' => null,
        ]);
    }

    public function forTask(Task $task): static
    {
        return $this->state(fn (array $attributes) => [
            'task_id' => $task->id,
        ]);
    }
}

// ============================================================================
// TaskChecklistItemFactory
// ============================================================================
class TaskChecklistItemFactory extends Factory
{
    protected $model = TaskChecklistItem::class;

    public function definition(): array
    {
        $isCompleted = fake()->boolean(30);

        return [
            'task_id' => Task::factory(),
            'title' => fake()->sentence(),
            'is_completed' => $isCompleted,
            'completed_by' => $isCompleted ? User::factory() : null,
            'completed_at' => $isCompleted ? now() : null,
            'order' => fake()->numberBetween(0, 20),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_completed' => true,
            'completed_by' => User::factory(),
            'completed_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_completed' => false,
            'completed_by' => null,
            'completed_at' => null,
        ]);
    }
}

// ============================================================================
// MilestoneFactory
// ============================================================================
class MilestoneFactory extends Factory
{
    protected $model = Milestone::class;

    public function definition(): array
    {
        $status = fake()->randomElement(['pending', 'completed', 'missed']);

        return [
            'project_id' => Project::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'due_date' => fake()->dateTimeBetween('now', '+6 months'),
            'status' => $status,
            'completed_at' => $status === 'completed' ? now() : null,
            'order' => fake()->numberBetween(0, 10),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'due_date' => fake()->dateTimeBetween('now', '+3 months'),
        ]);
    }
}

// ============================================================================
// TaskDependencyFactory
// ============================================================================
class TaskDependencyFactory extends Factory
{
    protected $model = TaskDependency::class;

    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'depends_on_task_id' => Task::factory(),
            'dependency_type' => fake()->randomElement(['finish_to_start', 'start_to_start', 'finish_to_finish']),
            'lag_days' => fake()->numberBetween(0, 5),
        ];
    }
}

// ============================================================================
// InvitationFactory
// ============================================================================
class InvitationFactory extends Factory
{
    protected $model = Invitation::class;

    public function definition(): array
    {
        return [
            'invitable_type' => Team::class,
            'invitable_id' => Team::factory(),
            'inviter_id' => User::factory(),
            'invitee_email' => fake()->safeEmail(),
            'invitee_id' => null,
            'role' => fake()->randomElement(['admin', 'member']),
            'token' => Str::random(64),
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ];
    }

    public function toProject(): static
    {
        return $this->state(fn (array $attributes) => [
            'invitable_type' => Project::class,
            'invitable_id' => Project::factory(),
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'accepted',
            'invitee_id' => User::factory(),
            'accepted_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expires_at' => fake()->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }
}
