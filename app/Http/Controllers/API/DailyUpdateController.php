<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\DailyTask;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Models\EmployeeChecking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class DailyUpdateController extends Controller
{
    use ResponseTrait;
    public function index()
    {
        try {
            $user = Auth::user();
            $today = now()->format('Y-m-d');
            $dailyTasks = DailyTask::where('daily_tasks.user_id', $user->id)
                ->whereDate('daily_tasks.task_date', $today)
                ->join('employee_checkings', 'daily_tasks.employee_checking_id', '=', 'employee_checkings.id')
                ->select(
                    'daily_tasks.id',
                    'daily_tasks.user_id',
                    'daily_tasks.employee_checking_id',
                    'daily_tasks.task_date',
                    'daily_tasks.created_at',
                    'daily_tasks.updated_at',
                    'employee_checkings.total_hours',
                    'employee_checkings.lat',
                    'employee_checkings.long'
                )->with('descriptions')
                ->get();
            return $this->sendResponse($dailyTasks, 'Daily tasks retrieved successfully');
        } catch (Exception $e) {
            Log::error('Failed to retrieve daily tasks: ' . $e->getMessage());
            return $this->sendError('Something went wrong', 500);
        }
    }
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'employee_checking_id' => 'required|exists:employee_checkings,id',
            'tasks' => 'required|array',
            'tasks.*.task_name' => 'required|string|max:255',
            'tasks.*.description' => 'required|string',
            'task_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 422);
        }

        // Get authenticated user
        $user = Auth::user();

        // Verify the employee_checking belongs to the user
        $checking = EmployeeChecking::where('id', $request->employee_checking_id)
            ->where('user_id', $user->id)
            ->whereDate('date', $request->task_date)
            ->first();

        if (!$checking) {
            return $this->sendError('Invalid or unauthorized employee checking record for the specified date', 403);
        }

        // Begin transaction
        DB::beginTransaction();

        try {
            // Create or find the daily task for the user and date
            $dailyTask = DailyTask::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'task_date' => $request->task_date,
                    'employee_checking_id' => $request->employee_checking_id,
                ]
            );

            $storedDescriptions = [];
            // Store each task description
            foreach ($request->tasks as $task) {
                $description = $dailyTask->descriptions()->create([
                    'task_name' => $task['task_name'],
                    'description' => $task['description'],
                ]);
                $storedDescriptions[] = $description;
            }

            // Load the daily task with all descriptions
            $dailyTask->load('descriptions');

            DB::commit();
            return $this->sendResponse($dailyTask, 'Data stored successfully');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to store tasks: ' . $e->getMessage());
            return $this->sendError('Something went wrong', 500);
        }
    }
    //update task
    public function update(Request $request, DailyTask $dailyTask): \Illuminate\Http\JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'employee_checking_id' => 'required|exists:employee_checkings,id',
            'tasks' => 'required|array',
            'tasks.*.id' => 'sometimes|exists:daily_task_descriptions,id',
            'tasks.*.task_name' => 'required|string|max:255',
            'tasks.*.description' => 'required|string',
            'task_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 422);
        }

        // Get authenticated user
        $user = Auth::user();

        // Verify the employee_checking and daily task belongs to the user
        $checking = EmployeeChecking::where('id', $request->employee_checking_id)
            ->where('user_id', $user->id)
            ->whereDate('date', $request->task_date)
            ->first();

        if (!$checking || $dailyTask->user_id !== $user->id || $dailyTask->employee_checking_id !== $request->employee_checking_id) {
            return $this->sendError('Invalid or unauthorized employee checking or daily task record', 403);
        }

        // Begin transaction
        DB::beginTransaction();

        try {
            // Update daily task attributes
            $dailyTask->update([
                'task_date' => $request->task_date,
                'employee_checking_id' => $request->employee_checking_id,
            ]);

            // Get existing description IDs
            $existingDescriptionIds = $dailyTask->descriptions->pluck('id')->toArray();
            $newDescriptionIds = array_filter(array_column($request->tasks, 'id'));

            // Delete descriptions that are not in the request
            $dailyTask->descriptions()
                ->whereNotIn('id', $newDescriptionIds)
                ->delete();

            $storedDescriptions = [];
            // Update or create descriptions
            foreach ($request->tasks as $task) {
                $description = $dailyTask->descriptions()->updateOrCreate(
                    ['id' => $task['id'] ?? null],
                    [
                        'task_name' => $task['task_name'],
                        'description' => $task['description'],
                    ]
                );
                $storedDescriptions[] = $description;
            }

            // Load the daily task with all descriptions
            $dailyTask->load('descriptions');

            DB::commit();
            return $this->sendResponse($dailyTask, 'Data updated successfully');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update tasks: ' . $e->getMessage());
            return $this->sendError('Something went wrong', 500);
        }
    }

    //delete task description
    public function deleteTaskDescription($dailyTaskId, $descriptionId)
    {
        try {
            $user = Auth::user();
            $dailyTask = DailyTask::where('id', $dailyTaskId)
                ->where('user_id', $user->id)
                ->first();

            if (!$dailyTask) {
                return $this->sendError('Daily task not found or unauthorized', 404);
            }

            $description = $dailyTask->descriptions()->where('id', $descriptionId)->first();

            if (!$description) {
                return $this->sendError('Task description not found', 404);
            }

            $description->delete();
            return $this->sendResponse([], 'Task description deleted successfully');
        } catch (Exception $e) {
            Log::error('Failed to delete task description: ' . $e->getMessage());
            return $this->sendError('Something went wrong', 500);
        }
    }
}
