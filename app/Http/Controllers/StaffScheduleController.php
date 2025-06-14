<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\StaffSchedule;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\StaffScheduleNotify;

class StaffScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page = 'Staff Schedules';

        $staffs = Staff::orderBy('id', 'DESC')
        ->whereNull('deleted_at')
        ->pluck('firstname','id')
        ->toArray();

        return view('pages.back.v_staffschedule', compact('page','staffs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $staffschedules = StaffSchedule::with('staff')
            ->orderBy('id', 'DESC')
            ->get();

        $formattedData = $staffschedules->map(function ($item) {

            $fullname = $item->staff->firstname.' '.$item->staff->lastname;

            return [
                'name' => $fullname ?? 'N/A',
                'timein' => \Carbon\Carbon::parse($item->time_in)->format('h:i A'),
                'timeout' => \Carbon\Carbon::parse($item->time_out)->format('h:i A'),
                'scheduleday' => e($item->schedule_day),
                'actions' => '
    
                 <a class="edit-btn" href="javascript:void(0)" 
                    data-id="' .  $item->id . '"
                    data-staffid="' .  $item->staff_id . '"
                    data-timein="' . $item->time_in . '"
                    data-timeout="' . $item->time_out . '"
                    data-scheduleday="' . $item->schedule_day . '"
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
        $request->validate([
            'staff' => 'required|integer',
            'timein' => 'required|date_format:H:i',
            'timeout' => 'required|date_format:H:i',
            'schedule_day' => 'required|string',
        ]);

        StaffSchedule::create([
            'staff_id' => $request->staff,
            'time_in' => $request->timein,
            'time_out' => $request->timeout,
            'schedule_day' => $request->schedule_day
        ]);

        return response()->json([
            'message' => 'Schedule saved successfully',
            'type' => 'success'
        ]);
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

        
        $schedule = StaffSchedule::find($id);

        if (!$schedule) {
            return response()->json(['error' => 'Schedule not found'], 404);
        }

        $request->validate([
            'staff' => 'required|integer',
            'timein' => 'required',
            'timeout' => 'required',
            'schedule_day' => 'required|string',
        ]);

        $schedule->update([
            'staff_id' => $request->staff,
            'time_in' => $request->timein,
            'time_out' => $request->timeout,
            'schedule_day' => $request->schedule_day
        ]);
        
        $staff = Staff::findOrFail($request->staff);

         $staff->notify(new StaffScheduleNotify([
            'schedule_day' => $request->schedule_day
        ]));

        return response()->json([
            'message' => 'Schedule updated successfully',
            'type' => 'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $schedule = StaffSchedule::find($id);

        if (!$schedule) {
            return response()->json(['error' => 'Schedule not found'], 404);
        }

        DB::transaction(function () use ($schedule) {
            $schedule->delete();
        });

        return response()->json(['message' => 'Schedule deleted successfully', 'type' => 'success']);
    }
}
