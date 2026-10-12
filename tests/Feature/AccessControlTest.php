<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\DailyWorkUpdate;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(UserRole $role, ?User $manager = null, ?string $email = null): User
    {
        return User::create([
            'name' => ucfirst($role->value).' '.uniqid(),
            'email' => $email ?? uniqid().'@example.test',
            'password' => 'Secret123',
            'role' => $role,
            'manager_id' => $manager?->id,
            'is_active' => true,
        ]);
    }

    private function makeTask(User $assignee, User $creator, TaskStatus $status = TaskStatus::ToDo, ?Carbon $due = null): Task
    {
        return Task::create([
            'title' => 'Sample task',
            'description' => 'Details',
            'start_date' => Carbon::today()->subDays(5),
            'due_date' => $due ?? Carbon::today()->addDays(3),
            'priority' => TaskPriority::Medium,
            'status' => $status,
            'assigned_to' => $assignee->id,
            'created_by' => $creator->id,
        ]);
    }

    public function test_employee_cannot_open_employee_directory(): void
    {
        $employee = $this->makeUser(UserRole::Employee);

        $this->actingAs($employee)->get(route('employees.index'))->assertForbidden();
    }

    public function test_manager_cannot_view_employee_outside_their_team(): void
    {
        $managerA = $this->makeUser(UserRole::Manager);
        $managerB = $this->makeUser(UserRole::Manager);
        $outsider = $this->makeUser(UserRole::Employee, $managerB);

        $this->actingAs($managerA)->get(route('employees.show', $outsider))->assertForbidden();
    }

    public function test_employee_cannot_submit_update_for_another_employees_task(): void
    {
        $manager = $this->makeUser(UserRole::Manager);
        $owner = $this->makeUser(UserRole::Employee, $manager);
        $intruder = $this->makeUser(UserRole::Employee, $manager);
        $task = $this->makeTask($owner, $manager);

        $this->actingAs($intruder)
            ->post(route('work-updates.store'), [
                'task_id' => $task->id,
                'work_date' => Carbon::today()->toDateString(),
                'completed_work' => 'Did things',
                'todays_progress' => 'Progressing',
            ])
            ->assertSessionHasErrors('task_id');

        $this->assertDatabaseCount('daily_work_updates', 0);
    }

    public function test_completed_tasks_are_never_overdue(): void
    {
        $manager = $this->makeUser(UserRole::Manager);
        $employee = $this->makeUser(UserRole::Employee, $manager);

        $this->makeTask($employee, $manager, TaskStatus::Completed, Carbon::yesterday());
        $openOverdue = $this->makeTask($employee, $manager, TaskStatus::InProgress, Carbon::yesterday());

        $overdue = Task::query()->overdue()->pluck('id')->all();

        $this->assertSame([$openOverdue->id], $overdue);
    }

    public function test_employee_sees_only_own_tasks_on_dashboard(): void
    {
        $manager = $this->makeUser(UserRole::Manager);
        $me = $this->makeUser(UserRole::Employee, $manager);
        $colleague = $this->makeUser(UserRole::Employee, $manager);

        $mine = $this->makeTask($me, $manager);
        $this->makeTask($colleague, $manager);

        $this->actingAs($me)->get(route('dashboard'))
            ->assertOk()
            ->assertSee($mine->title);

        $this->assertSame(1, Task::query()->visibleTo($me)->count());
    }

    public function test_inactive_user_is_signed_out(): void
    {
        $manager = $this->makeUser(UserRole::Manager);
        $employee = $this->makeUser(UserRole::Employee, $manager);
        $employee->update(['is_active' => false]);

        $this->actingAs($employee)->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_work_update_model_links_to_employee_and_task(): void
    {
        $manager = $this->makeUser(UserRole::Manager);
        $employee = $this->makeUser(UserRole::Employee, $manager);
        $task = $this->makeTask($employee, $manager);

        $update = DailyWorkUpdate::create([
            'task_id' => $task->id,
            'employee_id' => $employee->id,
            'work_date' => Carbon::today(),
            'completed_work' => 'Done',
            'todays_progress' => 'Ongoing',
        ]);

        $this->assertTrue($update->task->is($task));
        $this->assertTrue($update->employee->is($employee));
    }
}
