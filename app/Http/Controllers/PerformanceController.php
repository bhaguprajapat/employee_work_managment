<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportFilterRequest;
use App\Models\User;
use App\Services\WorkReportService;
use Illuminate\View\View;

class PerformanceController extends Controller
{
    public function __construct(private readonly WorkReportService $reports)
    {
    }

    /**
     * Factual performance indicators. Employees see their own; managers and admins
     * pick someone within their scope. No automated employment decisions are made.
     */
    public function index(ReportFilterRequest $request): View
    {
        $viewer = $request->user();
        $filters = $request->validated();

        $target = $this->resolveTarget($viewer, $filters['employee_id'] ?? null);

        return view('performance.index', [
            'target' => $target,
            'metrics' => $target ? $this->reports->performanceFor($target, $filters['from'] ?? null, $filters['to'] ?? null) : null,
            'employees' => $this->selectableEmployees($viewer),
            'filters' => $filters,
        ]);
    }

    /** Employees always evaluate themselves; managers and admins must stay within scope. */
    private function resolveTarget(User $viewer, ?int $requestedId): ?User
    {
        if ($viewer->isEmployee()) {
            return $viewer;
        }

        if ($requestedId === null) {
            return null;
        }

        $employee = User::query()->where('role', 'employee')->findOrFail($requestedId);

        abort_unless($viewer->managesEmployee($employee), 403);

        return $employee;
    }

    private function selectableEmployees(User $viewer)
    {
        if ($viewer->isEmployee()) {
            return collect();
        }

        return User::query()
            ->where('role', 'employee')
            ->when($viewer->isManager(), fn ($q) => $q->where('manager_id', $viewer->id))
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
