<?php

namespace App\Http\Controllers;

use App\Models\Shelter;
use App\Models\User;
use App\Models\FoodInventory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FoodInventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page = 'Food Inventory';

        return view('pages.back.v_foodinventory', compact('page'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $userShelter = Shelter::where('user_id', auth()->id())->first();

        if (!$userShelter) {
            return response()->json(['data' => [], 'message' => 'User shelter not found'], 200);
        } 

         // Get the start and end dates from the request
         $startDate = $request->query('start');
         $endDate = $request->query('end');
 
         // Build the query
         $query = FoodInventory::where('shelter_id', $userShelter->id);

         if ($startDate && $endDate) {
            $query->whereBetween('date_range', [$startDate, $endDate]);
        }

        // Get the filtered expenses
        $foodinventories= $query->get();
        
        $formattedData = $foodinventories->map(function ($item) {
            return [
                'name' => $item->name,
                'stockin' => $item->stock_in,
                'stockout' => $item->stock_out,
                'remainingstock' => $item->remaining_stock,
                'category' => $item->category,
                'daterange' => Carbon::parse($item->date_range)->format('F d, Y'),
                'actions' => '

                 <a class="edit-btn" href="javascript:void(0)" 
                    data-id="' .  $item->id . '"
                    data-name="' . $item->name . '"
                    data-stockin="' . $item->stock_in . '"
                    data-stockout="' . $item->stock_out . '"
                    data-category="' . $item->category . '"
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
        $userShelter = Shelter::where('user_id', auth()->id())->first();

        if (!$userShelter) {
            return response()->json(['data' => [], 'message' => 'User shelter not found'], 200);
        }

        // Validate the request
        $validatedData = $request->validate([
            'name' => 'required|array',
            'stockin' => 'required|array',
            'stockout' => 'nullable|array',
            'category' => 'required|array',
            'name.*' => 'required|string|max:255',
            'stockin.*' => 'required|integer',
            'stockout.*' => 'nullable|integer',
            'category.*' => 'required|string|max:255',
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {

            foreach ($validatedData['name'] as $index => $name) {
                FoodInventory::create([
                    'shelter_id' => $userShelter->id,
                    'name' => $name,
                    'stock_in' => $validatedData['stockin'][$index],
                    'stock_out' => $validatedData['stockout'][$index],
                    'category' => $validatedData['category'][$index],
                ]);
            }

            DB::commit();

            return response()->json([
                'type' => 'success',
                'message' => 'Food item saved successfully!'
            ], 200);
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Failed to save food item', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'type' => 'error',
                'message' => 'Failed to save food item. Please try again!'
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
       $userShelter = Shelter::where('user_id', auth()->id())->first();

        if (!$userShelter) {
            return response()->json(['data' => [], 'message' => 'User shelter not found'], 200);
        }

        // Validate the request
        $validatedData = $request->validate([
            'name' => 'required|array',
            'stockin' => 'required|array',
            'stockout' => 'nullable|array',
            'category' => 'required|array',
            'name.*' => 'required|string|max:255',
            'stockin.*' => 'required|integer',
            'stockout.*' => 'nullable|integer',
            'category.*' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
         
            $inventory = FoodInventory::where('id', $id)->where('shelter_id', $userShelter->id)->first();

            if (!$inventory) {
                return response()->json(['type' => 'error', 'message' => 'Inventory not found.'], 404);
            }

            // Update the expense fields
            foreach ($validatedData['name'] as $index => $name) {
                $inventory->update([
                    'shelter_id' => $userShelter->id,
                    'name' => $name,
                    'stock_in' => $validatedData['stockin'][$index],
                    'stock_out' => $validatedData['stockout'][$index],
                    'category' => $validatedData['category'][$index],
                ]);
            }

            DB::commit();

            return response()->json([
                'type' => 'success',
                'message' => 'Food item updated successfully!'
            ], 200);
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Failed to update food item', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'type' => 'error',
                'message' => 'Failed to update food item. Please try again!'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $inventory = FoodInventory::find($id);

        if (!$inventory) {
            return response()->json(['error' => 'Food item not found'], 404);
        }

        DB::transaction(function () use ($inventory) {
            $inventory->delete();
        });

        return response()->json(['message' => 'Food item deleted successfully', 'type' => 'success']);
    }
}
