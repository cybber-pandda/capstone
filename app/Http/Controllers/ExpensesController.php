<?php

namespace App\Http\Controllers;

use App\Models\Shelter;
use App\Models\User;
use App\Models\Expense;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ExpensesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page = 'Expenses';

        return view('pages.back.v_expenses', compact('page'));
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
        $query = Expense::where('shelter_id', $userShelter->id);

        // Apply date range filter if both start and end dates are provided
        if ($startDate && $endDate) {
            $query->whereBetween('date_range', [$startDate, $endDate]);
        }

        // Get the filtered expenses
        $expenses = $query->get();

        $formattedData = $expenses->map(function ($item) {
            return [
                'name' => $item->name,
                'price' => $item->price,
                'qty' => $item->qty,
                'proofreceipt' => $item->proof_receipt,
                'daterange' => Carbon::parse($item->date_range)->format('F d, Y'),
                'actions' => '
    
                 <a class="edit-btn" href="javascript:void(0)" 
                    data-id="' .  $item->id . '"
                    data-name="' . $item->name . '"
                    data-qty="' . $item->qty . '"
                    data-price="' . $item->price . '"
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
            'price' => 'required|array',
            'qty' => 'required|array',
            'name.*' => 'required|string|max:255',
            'price.*' => 'required|integer',
            'qty.*' => 'required|integer',
            'proof_receipt.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate image files
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            foreach ($validatedData['name'] as $index => $name) {
                // Get the uploaded file
                $imgFile = $request->file('proof_receipt')[$index];

                // Generate a unique filename
                $filename = Str::random(10) . '_' . time() . '.' . $imgFile->getClientOriginalExtension();

                // Define the image path
                $imagePath = 'assets/uploads/' . $filename;

                // Move the uploaded file to the designated folder
                $imgFile->move(public_path('assets/uploads'), $filename);

                // Create the expense record in the database
                Expense::create([
                    'shelter_id' => $userShelter->id,
                    'name' => $name,
                    'qty' => $validatedData['qty'][$index],
                    'price' => $validatedData['price'][$index],
                    'proof_receipt' => $imagePath, // Save the path to the database
                ]);
            }

            DB::commit();

            return response()->json([
                'type' => 'success',
                'message' => 'Expenses saved successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to save expenses', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'type' => 'error',
                'message' => 'Failed to save expenses. Please try again!'
            ], 500);
        }
    }



    /**
     * Update the specified expense in storage.
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
            'price' => 'required|array',
            'qty' => 'required|array',
            'name.*' => 'required|string|max:255',
            'price.*' => 'required|integer',
            'qty.*' => 'required|integer',
            'proof_receipt.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Proof receipt can be nullable
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Fetch the expense by its ID
            $expense = Expense::where('id', $id)->where('shelter_id', $userShelter->id)->first();

            if (!$expense) {
                return response()->json(['type' => 'error', 'message' => 'Expense not found.'], 404);
            }

            foreach ($validatedData['name'] as $index => $name) {
                // Check if a new proof receipt is uploaded
                if ($request->hasFile('proof_receipt.' . $index)) {
                    $imgFile = $request->file('proof_receipt')[$index];

                    // Generate a unique filename
                    $filename = Str::random(10) . '_' . time() . '.' . $imgFile->getClientOriginalExtension();

                    // Define the image path
                    $imagePath = 'assets/uploads/' . $filename;

                    // Move the uploaded file to the designated folder
                    $imgFile->move(public_path('assets/uploads'), $filename);

                    // Update the expense with the new image path
                    $expense->update([
                        'proof_receipt' => $imagePath,
                    ]);
                }

                // Update other fields
                $expense->update([
                    'name' => $name,
                    'qty' => $validatedData['qty'][$index],
                    'price' => $validatedData['price'][$index],
                ]);
            }

            DB::commit();

            return response()->json([
                'type' => 'success',
                'message' => 'Expense updated successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update expense', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'type' => 'error',
                'message' => 'Failed to update expense. Please try again!'
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
        $expense = Expense::find($id);

        if (!$expense) {
            return response()->json(['error' => 'Expense not found'], 404);
        }

        DB::transaction(function () use ($expense) {
            $expense->delete();
        });

        return response()->json(['message' => 'Expense deleted successfully', 'type' => 'success']);
    }
}
