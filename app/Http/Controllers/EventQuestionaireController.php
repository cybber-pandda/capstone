<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Shelter;
use App\Models\EventSetting;
use App\Models\EventSettingQuestion;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EventQuestionaireController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page = 'Event Questionaire Settings';
        return view('pages.back.v_eventsettings', compact('page'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   

        $user = User::getCurrentUser();

        $eventsettings = EventSetting::with('questions')
            ->where('deleted_at', null)
            ->where('user_id', $user->id)
            ->orderBy('id', 'DESC')
            ->get();

        $formattedData = $eventsettings->map(function ($item) {
            // Build the list of questions as <ul> with delete buttons for each question
            $questionList = '<ul>';
            foreach ($item->questions as $question) {
                $questionList .= '<li>' . $question->question .
                    ' <a href="javascript:void(0)" class="delete-question-btn d-none" data-question-id="' . $question->id . '">
                    <i class="bi bi-trash fs-4"></i>
                   </a>
               </li>';
            }
            $questionList .= '</ul>';

            return [
                'event_title' => $item->event_title,
                'description' => $item->description,
                'start_date' => $item->start_date,
                'end_date' => $item->end_date,
               'event_time' => Carbon::parse($item->event_time)->format('h:i A'),
                'location' => $item->location,
                'capacity' => $item->capacity,
                'status' => $item->status,
                'question' => $questionList,
                'actions' => '
                <a class="edit-btn" href="javascript:void(0)"
                    data-id="' . $item->id . '"
                    data-eventtitle="' . $item->event_title . '"
                    data-title="' . $item->title . '"
                    data-description="' . $item->description . '"
                    data-startdate="' . $item->start_date . '"
                    data-enddate="' . $item->end_date . '"
                    data-eventtime="' . $item->event_time . '"
                    data-location="' . $item->location . '"
                    data-capacity="' . $item->capacity . '"
                    data-status="' . $item->status . '"
                    data-questions=\'' . json_encode($item->questions->toArray()) . '\'
                    data-modaltitle="Edit">
                    <i class="bi bi-pencil-square fs-3"></i>
                </a>

                <a class="delete-btn" href="javascript:void(0)" data-id="' . $item->id . '">
                    <i class="bi bi-trash fs-3"></i>
                </a>'
            ];
        });

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
            'event' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'event_time' => 'required',
            'location' => 'required|string',
            'capacity' => 'required|integer',
            'status' => 'required|string',
            // 'question' => 'required|array',
            // 'questions.*' => 'required|string|max:255',
        ]);

        // Start a database transaction
        DB::beginTransaction();

        $userShelter = Shelter::where('user_id', auth()->id())->first();

        if (!$userShelter) {
            return response()->json(['message' => 'User shelter not found'], 200);
        }

        try {
            // Create or store the event setting
            $eventSetting = EventSetting::create([
                'user_id' =>  auth()->id(),
                'shelter_id' => $userShelter->id,
                'event_title' => $validatedData['event'],
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'event_time' => $request->event_time,
                'location' => $request->location,
                'capacity' => $request->capacity,
                'status' => $request->status
            ]);

            // Store the questions
            // foreach ($validatedData['question'] as $question) {
            //     EventSettingQuestion::create([
            //         'event_setting_id' => $eventSetting->id,
            //         'question' => $question
            //     ]);
            // }

            // Commit the transaction
            DB::commit();

            // Respond with a success message
            return response()->json([
                'type' => 'success',
                'message' => 'Event settings saved successfully!'
            ], 200);
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();

            Log::error('Failed to save event settings', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'type' => 'error',
                'message' => 'Failed to save event settings. Please try again!'
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
        $validated = $request->validate([
            'event' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'event_time' => 'required',
            'location' => 'required|string',
            'capacity' => 'required|integer',
            'status' => 'required|string',
            // 'question' => 'array',
            // 'question.*' => 'string'
        ]);

        $eventSetting = EventSetting::findOrFail($id);
        $eventSetting->event_title = $validated['event'];
        $eventSetting->description = $request->description;
        $eventSetting->start_date = $request->start_date;
        $eventSetting->end_date = $request->end_date;
        $eventSetting->event_time = $request->event_time;
        $eventSetting->location = $request->location;
        $eventSetting->capacity = $request->capacity;
        $eventSetting->status = $request->status;
        $eventSetting->save();

        // Update or add questions
        // $eventSetting->questions()->delete(); // Remove old questions
        // foreach ($validated['question'] as $questionText) {
        //     $eventSetting->questions()->create(['question' => $questionText]);
        // }

        return response()->json([
            'type' => 'success',
            'message' => 'Event settings updated successfully!'
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
        $eventsetting =  EventSetting::find($id);

        if (!$eventsetting) {
            return response()->json(['error' => 'Event setting not found'], 404);
        }

        DB::transaction(function () use ($eventsetting) {
            $eventsetting->delete();
        });

        return response()->json(['message' => 'Event deleted successfully', 'type' => 'success']);
    }
}
