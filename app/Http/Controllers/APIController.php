<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

use App\Models\Delivery;
use App\Models\DeliveryHistory;

class APIController extends Controller
{

    public function logLocation(Request $request, $id)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $delivery = Delivery::findOrFail($id);

        DeliveryHistory::create([
            'delivery_id' => $delivery->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'remarks' => $request->remarks ?? 'Updated from geolocation',
            'logged_at' => now(),
        ]);

        return response()->json(['message' => 'Location logged']);
    }

    public function deliveryTrackingSSE($id)
    {
        $delivery = Delivery::with('latestHistory')->find($id);

        if ($delivery && $delivery->latestHistory) {
            $data = [
                'lat' => $delivery->latestHistory->latitude,
                'lng' => $delivery->latestHistory->longitude,
            ];
            return response("data: " . json_encode($data) . "\n\n", 200)
                ->header('Content-Type', 'text/event-stream')
                ->header('Cache-Control', 'no-cache');
        }

        return response("data: {}\n\n", 200)
            ->header('Content-Type', 'text/event-stream')
            ->header('Cache-Control', 'no-cache');
    }

}
