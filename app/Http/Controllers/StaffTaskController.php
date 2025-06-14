<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\StaffSchedule;
use App\Models\StaffTask;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\TaskNotification;

class StaffTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page = 'Staff Tasks';

        $staffs = Staff::orderBy('id', 'DESC')
            ->whereNull('deleted_at')
            ->pluck('firstname', 'id')
            ->toArray();

        return view('pages.back.v_stafftask', compact('page', 'staffs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Group tasks by staff_id
        $stafftasks = StaffTask::with('staff')
            ->whereNull('deleted_at')
            ->orderBy('id', 'DESC')
            ->get()
            ->groupBy('staff_id'); // Group by staff_id to collect all tasks for each staff

        $formattedData = $stafftasks->map(function ($tasks, $staffId) {
            // Get the first task to retrieve the staff info
            $firstTask = $tasks->first();
            $fullname = $firstTask->staff->firstname . ' ' . $firstTask->staff->lastname;
            $taskList = '<ul>';
            $taskNumber = 1;
            foreach ($tasks as $task) {
                // Determine status based on percentage and task status
                if ($task->task_status == 0) {
                    $status = 'Pending';
                    $feedback = 'This task is yet to start.';
                } elseif ($task->task_percentage > 0 && $task->task_percentage < 75) {
                    $status = 'Below 75%';
                    $feedback = 'Performance is Poor. Keep pushing forward!';
                } elseif ($task->task_percentage == 75) {
                    $status = 'Exactly 75%';
                    $feedback = 'Performance is Intermediate. Good job!';
                } elseif ($task->task_percentage > 75) {
                    $status = 'Above 75%';
                    $feedback = 'Performance is Very Good. Excellent progress!';
                } else {
                    $status = 'Unknown'; // Fallback for unexpected values
                    $feedback = 'Status unknown. Please check.';
                }
                

                // Build the task list with task number, status, and performance feedback
                $taskList .= '<li class="mb-3">
                                <strong>TASK ' . $taskNumber . ':</strong> ' . $task->task .
                    ' <strong>Percentage:</strong> ' . $task->task_percentage . '% ' .
                    '<strong>Status:</strong> ' . $status .
                    '<br><em>Feedback:</em> ' . $feedback . '
                                <a class="btn btn-primary-soft btn-sm add-task-percent" href="javascript:void(0)"    data-id="' . $task->id . '" >Add Task Percentage <i class="bi bi-percent"></i></a>
                              </li>';
                $taskNumber++; // Increment the task counter
            }
            $taskList .= '</ul>';

            return [
                'name' => $fullname ?? 'N/A',
                'tasklist' => $taskList,
                'actions' => '
                <a class="edit-btn" href="javascript:void(0)" 
                    data-id="' . $firstTask->id . '"
                    data-staffid="' . $firstTask->staff_id . '"
                    data-tasklist=\'' . json_encode($tasks->toArray()) . '\'
                    data-modaltitle="Edit">
                    <i class="bi bi-pencil-square fs-3"></i>
                </a>
                <a class="delete-btn" href="javascript:void(0)" data-id="' . $firstTask->staff_id  . '">
                    <i class="bi bi-trash fs-3"></i>
                </a>'
            ];
        });

        return response()->json(['data' => $formattedData->values()]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the request
        $validatedData = $request->validate([
            'staff' => 'required|integer',
            'task' => 'required|array',
            'tasks.*' => 'required|string|max:255',
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {

            $tasks = []; // Collect all tasks for notification

            foreach ($validatedData['task'] as $task) {
                // Save the task to the database
                $staffTask = StaffTask::create([
                    'staff_id' => $request->staff,
                    'task' => $task
                ]);

                // Add each task to the tasks array
                $tasks[] = [
                    'task' => $task,
                    'task_id' => $staffTask->id
                ];
            }

            // Retrieve staff and schedule details
            $staff = Staff::findOrFail($request->staff);
            $staffSchedule = StaffSchedule::where('staff_id', $request->staff)->firstOrFail();

            // Send a single notification with all tasks and schedule details
            $staff->notify(new TaskNotification([
                'tasks' => $tasks, // Now passing all tasks
                'schedule_time_in' => $staffSchedule->time_in,
                'schedule_time_out' => $staffSchedule->time_out,
                'schedule_day' => $staffSchedule->schedule_day,
            ]));

            DB::commit();

            return response()->json([
                'type' => 'success',
                'message' => 'Task saved successfully!'
            ], 200);
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Failed to save task', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'type' => 'error',
                'message' => 'Failed to save task. Please try again!'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validate the request
        $validated = $request->validate([
            'staff' => 'required|integer',
            'task' => 'required|array',
            'task.*' => 'required|string|max:255'
        ]);

        DB::beginTransaction();

        try {
            $tasks = []; // Collect all updated tasks for notification

            // Retrieve all existing staff tasks for the given staff
            $staffTasks = StaffTask::where('staff_id', $validated['staff'])->get();

            // Loop through each task and update it, collecting updated tasks for notification
            foreach ($staffTasks as $index => $staffTask) {
                if (isset($validated['task'][$index])) {
                    $staffTask->update([
                        'task' => $validated['task'][$index],
                    ]);

                    // Add updated task to tasks array for notification
                    $tasks[] = [
                        'task' => $validated['task'][$index],
                        'task_id' => $staffTask->id
                    ];
                }
            }

            // Retrieve staff and schedule details
            $staff = Staff::findOrFail($validated['staff']);
            $staffSchedule = StaffSchedule::where('staff_id', $validated['staff'])->firstOrFail();

            // Send a single notification with all updated tasks and schedule details
            $staff->notify(new TaskNotification([
                'tasks' => $tasks, // Now passing all updated tasks
                'schedule_time_in' => $staffSchedule->time_in,
                'schedule_time_out' => $staffSchedule->time_out,
                'schedule_day' => $staffSchedule->schedule_day,
            ]));

            DB::commit();

            return response()->json([
                'type' => 'success',
                'message' => 'Tasks updated and notification sent successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update tasks and send notification', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'type' => 'error',
                'message' => 'Failed to update tasks. Please try again!'
            ], 500);
        }
    }

    public function task_percentage(Request $request, $staffTaskId)
    {
        $validated = $request->validate([
            'percentage' => 'required|integer',
        ]);

        $staffTask = StaffTask::where('id', $staffTaskId)->firstOrFail();

        $staffTask->update([
            'task_percentage' => $validated['percentage'],
            'task_status' => ($validated['percentage'] !== 0 ? 1 : 0) 
        ]);

        // Return success response
        return response()->json([
            'type' => 'success',
            'message' => 'Task percentage updated successfully!'
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $stafftasks = StaffTask::where('staff_id', $id)->get();

        if ($stafftasks->isEmpty()) {
            return response()->json(['error' => 'No tasks found for this staff'], 404);
        }

        // Use DB transaction to ensure atomicity
        DB::transaction(function () use ($stafftasks) {
            // Batch delete all tasks associated with the staff
            StaffTask::where('staff_id', $stafftasks->first()->staff_id)->delete();
        });

        return response()->json(['message' => 'Staff tasks deleted successfully', 'type' => 'success']);
    }
}
