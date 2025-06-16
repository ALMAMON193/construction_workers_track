<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\DailyTask;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Models\EmployeeChecking;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;




class DailyUpdateController extends Controller
{
    use ResponseTrait;
    public function index(Request $request)
    {
        $user = Auth::user();
        // Fetch all daily tasks for the user with their descriptions
        $tasks = DailyTask::with('descriptions')
            ->where('user_id', $user->id)
            ->orderBy('task_date', 'desc')
            ->get();

        if ($tasks->isEmpty()) {
            return $this->sendError('No tasks found', 404);
        }

        // Get employee checkings for all task dates in one query for efficiency
        $taskDates = $tasks->pluck('task_date')->unique();
        $checkings = DB::table('employee_checkings')
            ->where('user_id', $user->id)
            ->whereIn('date', $taskDates)
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy('date'); // Group by date for easy lookup

        // Map checkings data to tasks
        $tasks->transform(function ($task) use ($checkings) {
            $taskDate = $task->task_date; // Assuming task_date is in 'Y-m-d' format

            // Find check-in/out data for this task's date
            $dateCheckings = $checkings->get($taskDate);

            if ($dateCheckings) {
                // Get the latest record for that date (if multiple exist)
                $latestChecking = $dateCheckings->first();

                // $task->current_location = $latestChecking->current_location ?? null;
                $task->lat = $latestChecking->lat ?? null;
                $task->long = $latestChecking->long ?? null;

                $task->total_hours = $latestChecking->total_hours ?? null;
            } else {
                $task->current_location = null;
                $task->total_hours = null;
            }

            // Remove unwanted fields
            unset($task->created_at);
            unset($task->updated_at);

            return $task;
        });

        return $this->sendResponse($tasks, 'Data fetched successfully');
    }
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
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

        // Check employee's checkout status
        $checking = EmployeeChecking::where('user_id', $user->id)
            ->where('status', 'check_out')
            ->whereDate('date', $request->task_date)
            ->first();

        if (!$checking) {
            return $this->sendError('You are not checked in today', 403);
        }
        // Create or find the daily task for the user and date
        $dailyTask = DailyTask::firstOrCreate([
            'user_id' => $user->id,
            'task_date' => $request->task_date,
        ]);

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

        return $this->sendResponse($dailyTask, 'Data stored successfully');
    }
    public function details($id)
    {
        // Fetch the daily task with descriptions
        $dailyTask = DailyTask::with('descriptions')->find($id);

        if (!$dailyTask) {
            return $this->sendError('Daily task not found', 404);
        }

        // Get the latest employee checking record for the task's user_id and date
        $checking = DB::table('employee_checkings')
            ->where('user_id', $dailyTask->user_id)
            ->where('date', $dailyTask->task_date) // Match by task_date
            ->orderBy('created_at', 'desc') // Get the latest record if multiple exist
            ->first();

        // Add the fields to the response
        $dailyTask->current_location = $checking->current_location ?? null;
        $dailyTask->total_hours = $checking->total_hours ?? null;

        // Remove unwanted fields from the main task
        unset($dailyTask->created_at);
        unset($dailyTask->updated_at);

        // Remove unwanted fields from each description
        if ($dailyTask->descriptions) {
            $dailyTask->descriptions->each(function ($description) {
                unset($description->created_at);
                unset($description->updated_at);
            });
        }

        return $this->sendResponse($dailyTask, 'Data fetched successfully');
    }
}
