<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\EventSetting;
use App\Models\EventVolunteerTask;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VolunteersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page = 'Volunteers';
        return view('pages.back.v_volunteers', compact('page'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = User::getCurrentUser();

        // Fetch event settings with volunteers, their tasks, questions, and answers
        $eventsettings = EventSetting::with(['eventVolunteers.user.userDetails', 'eventVolunteers.tasks', 'questions.answers'])
            ->where('deleted_at', null)
            ->where('user_id', $user->id)
            ->orderBy('id', 'DESC')
            ->get();

        // Flatten the data
        $formattedData = [];
        foreach ($eventsettings as $event) {
            // Collect questions and their answers
            $questionsAndAnswers = $event->questions->map(function ($question) {
                return [
                    'question' => $question->question,
                    'answers' => $question->answers->map(function ($answer) {
                        return [
                            'answer' => $answer->answer, // Assuming 'answer' is the field in EventVolunteerAnswer
                        ];
                    }),
                ];
            });

            // Loop through event volunteers and their tasks
            foreach ($event->eventVolunteers as $volunteer) {
                // Collect tasks for the volunteer
                $tasks = $volunteer->tasks->map(function ($task) {
                    return [
                        'taskId' => $task->id,
                        'task' => $task->task,
                        'taskStatus' => $task->task_status,
                        'actions' => '
                        <a class="edit-task-btn" href="javascript:void(0)"   
                            data-id="' . $task->id . '"
                            data-task="' . $task->task . '"
                            data-modaltitle="Edit">
                            <i class="bi bi-pencil-square fs-3"></i>
                        </a>

                        <a class="delete-task-btn" href="javascript:void(0)" data-id="' . $task->id . '">
                          <i class="bi bi-trash fs-3"></i>
                        </a>
                        '
    
                    ];
                });

                $formattedData[] = [
                    'evId' => $volunteer->id,
                    'eventUVId' => $volunteer->user_id,
                    'volunteerStatus' => $volunteer->status,
                    'event' => $event->event_title,
                    'location' => $event->location,
                    'volunteername' => $volunteer->user->userDetails->fullname ?? 'N/A', // Volunteer Name
                    'actions' => '
                    <a class="view-btn" href="javascript:void(0)"   
                        data-id="' . ($volunteer->id ?? 'N/A') . '"
                        data-modaltitle="View">
                            <i class="bi bi-eye fs-3"></i>
                    </a>

                    <a class="task-list-btn" href="javascript:void(0)"   
                        data-tasks=\'' . json_encode($tasks) . '\'
                        data-modaltitle="Task List">
                            <i class="bi bi-list-task fs-3"></i>
                    </a>
                ',
                    'questions' => $questionsAndAnswers, // Questions and Answers
                    'tasks' => $tasks, // Add tasks for this volunteer
                ];
            }
        }

        return response()->json(['data' => $formattedData]);
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
            'volunteer_id' => 'required|integer',
            'volunteer_user_id' => 'required|integer',
            'task' => 'required|array',
            'tasks.*' => 'required|string|max:255',
        ]);

        if ($request->volunteer_id) {
            $EventVolunteerId = $request->volunteer_id;
            DB::table('event_volunteers')->where('id', $EventVolunteerId)->update(['status' => 'Approved']);
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            foreach ($validatedData['task'] as $task) {
                EventVolunteerTask::create([
                    'user_id' => $request->volunteer_user_id,
                    'event_volunteer_id' => $request->volunteer_id,
                    'task' => $task
                ]);
            }

            // Commit the transaction
            DB::commit();

            return response()->json(['type' => 'success', 'message' => 'Task saved successfully!'], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to task', [
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
       
        $task = EventVolunteerTask::where('id', $id)->first();

        if (!$task) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        $request->validate([
            'task' => 'required|string',
        ]);

        $task ->update([
            'task' => $request->task,
        ]);

        return response()->json(['message' => 'Task updated successfully', 'type' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $task = EventVolunteerTask::find($id);
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully', 'type' => 'success']);

    }
}
