<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\DailyTask;
use Illuminate\Http\Request;
use App\Models\DailyTaskItem;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\DailyTaskResource;

class DailyUpdateController extends Controller
{
    use ResponseTrait;
    public function index()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return $this->sendError('Unauthorized', [], 401);
            }

            // Fetch only the logged-in user's daily task with its items
            $dailyTask = $user->dailyTask()->with('dailyTaskItems')->first();

            if (!$dailyTask) {
                return $this->sendResponse([], 'No daily tasks found for this user.');
            }

            return $this->sendResponse($dailyTask, 'Daily task retrieved successfully');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong', [], 500);
        }
    }
    public function store(Request $request)
    {
        $request->validate([
            'task_description' => 'required|string',
            'task_date' => 'nullable|date|date_format:Y-m-d|after_or_equal:today',
        ]);

        $user = auth()->user();

        if (!$user) {
            return $this->sendError('Unauthorized', [], 401);
        }

        try {
            return DB::transaction(function () use ($request, $user) {
                // Get current date
                $currentDate = now()->toDateString();

                // Find or create the user's daily task for today
                $dailyTask = $user->dailyTask()->firstOrCreate([
                    'user_id' => $user->id,
                    'task_date' => $currentDate
                ]);

                // Generate task number (Task 1, Task 2, etc.)
              // Generate task number (Task 1, Task 2, etc.)
$taskNumber = 'Task ' . ($dailyTask->dailyTaskItems()->count() + 1);

                // Create the task item with current date
                $dailyTask->dailyTaskItems()->create([
                    'task_number' => $taskNumber,
                    'task_description' => $request->task_description,
                    'task_date' => $currentDate,
                ]);

                // Reload the relationship to include the new item
                $dailyTask->load('dailyTaskItems');

                return $this->sendResponse(
                    $dailyTask,
                    'Daily task item created successfully',
                );
            });
        } catch (Exception $e) {
            return $this->sendError('Something went wrong', [], 500);
        }
    }

    public function details($id)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return $this->sendError('Unauthorized', [], 401);
            }

            // Find the daily task with items, belonging to the authenticated user
            $dailyTask = $user->dailyTask()
                ->with('dailyTaskItems')
                ->find($id);

            if (!$dailyTask) {
                return $this->sendError('Daily task not found', [], 404);
            }

            return $this->sendResponse(
                $dailyTask,
                'Daily task retrieved successfully'
            );
        } catch (Exception $e) {
            return $this->sendError('Something went wrong', [], 500);
        }
    }
}
