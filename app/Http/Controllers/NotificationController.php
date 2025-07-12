<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

use App\Models\Notification;

class NotificationController extends Controller
{

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Notification::where('user_id', Auth::id())
                ->latest();

            return DataTables::of($query)
                ->addColumn('message', fn($noti) => $noti->message)
                ->addColumn('type', fn($noti) => $noti->type)
                ->addColumn('time', fn($noti) => $noti->created_at->diffForHumans())
                ->addColumn('read_at', fn($noti) => $noti->read_at ? $noti->read_at->diffForHumans() : 'Unread')
                ->addColumn('checkbox', function ($noti) {
                    if (!$noti->read_at) {
                        return '<input type="checkbox" class="notification-checkbox" value="' . $noti->id . '">';
                    }
                    return '';
                })
                ->rawColumns(['checkbox', 'read_at']) // Allow HTML
                ->make(true);
        }

        $user = auth()->user();
        if ($user->role === 'b2b') {
            return view('pages.b2b.v_notification', [
                'page' => 'Notifications',
            ]);
        } else {
            return view('pages.notification', [
                'page' => 'Notifications',
            ]);
        }
    }

    public function notificationsApi()
    {
        $user = Auth::user();

        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($noti) {
                return [
                    'id' => $noti->id,
                    'message' => $noti->message,
                    'type' => $noti->type,
                    'time' => $noti->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'count' => $notifications->count(),
            'notifications' => $notifications
        ]);
    }

    public function markSelectedAsRead(Request $request)
    {
        $ids = $request->input('ids', []);

        Notification::whereIn('id', $ids)
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
