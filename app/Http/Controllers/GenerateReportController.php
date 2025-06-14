<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GenericExport;

class GenerateReportController extends Controller
{
    public function index()
    {
        $page = 'Generate Reports';

        return view('pages.back.v_generatereport', compact('page'));
    }


    public function generate_report(Request $request)
    {
        // Validate incoming request
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'table_value' => 'required|string',
            'pet_status' => 'nullable|string',
        ]);



        // Define the base query based on the table_value
        $query = null;

        switch ($validated['table_value']) {
            case 'animals':
                $query = DB::table('animals')
                    ->where('deleted_at', null)
                    ->whereBetween('registration_date', [$validated['start_date'], $validated['end_date']]);

                // Apply pet status filter if provided
                if (!empty($validated['pet_status'])) {
                    if ($validated['pet_status'] !== 'all') {
                        $query->where('current_status', $validated['pet_status']);
                    }
                    // If 'all', we do not apply any additional status filter
                }
                break;

            case 'adopters':
                $query = DB::table('adopters')
                    ->where('deleted_at', null)
                    ->whereBetween('registration_date', [$validated['start_date'], $validated['end_date']]);
                break;

            case 'staffs':
                $query = DB::table('staffs')
                    ->where('deleted_at', null)
                    ->whereBetween('registration_date', [$validated['start_date'], $validated['end_date']]);
                break;

            case 'food_inventories':
                $query = DB::table('food_inventories')->whereBetween('date_range', [$validated['start_date'], $validated['end_date']]);
                break;

            case 'expenses':
                $query = DB::table('expenses')->whereBetween('date_range', [$validated['start_date'], $validated['end_date']]);
                break;

            default:
                return response()->json(['message' => 'Invalid table selection'], 400);
        }

        // Execute the query and get results
        $results = $query->get();
        return response()->json($results);
    }


    public function generate_pdf(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'table_value' => 'required|string',
            'pet_status' => 'nullable|string',
        ]);

        $query = null;

        switch ($validated['table_value']) {
            case 'animals':
                $query = DB::table('animals')
                    ->whereNull('deleted_at')
                    ->whereBetween('registration_date', [$validated['start_date'], $validated['end_date']]);

                // Apply pet status filter if provided
                if (!empty($validated['pet_status']) && $validated['pet_status'] !== 'all') {
                    $query->where('current_status', $validated['pet_status']);
                }
                break;

            case 'adopters':
                $query = DB::table('adopters')
                    ->whereNull('deleted_at')
                    ->whereBetween('registration_date', [$validated['start_date'], $validated['end_date']]);
                break;

            case 'staffs':
                $query = DB::table('staffs')
                    ->whereNull('deleted_at')
                    ->whereBetween('registration_date', [$validated['start_date'], $validated['end_date']]);
                break;

            case 'food_inventories':
                $query = DB::table('food_inventories')->whereBetween('date_range', [$validated['start_date'], $validated['end_date']]);
                break;

            case 'expenses':
                $query = DB::table('expenses')->whereBetween('date_range', [$validated['start_date'], $validated['end_date']]);
                break;

            default:
                return response()->json(['message' => 'Invalid table selection'], 400);
        }

        // Execute the query and get results
        $data = $query->get();

        // Load the view and pass the data to it
        $pdf = Pdf::loadView('pdf.generate_report', [
            'data' => $data,
            'start' => $validated['start_date'],
            'end' => $validated['end_date'],
            'table_value' => $validated['table_value'],
        ])->setPaper('a4', 'landscape');

        return $pdf->download('generated_report_' . $validated['table_value'] . '_' . $validated['start_date'] . '_to_' . $validated['end_date'] . '.pdf');
    }


    public function generate_excel(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'table_value' => 'required|string',
            'pet_status' => 'nullable|string',
        ]);

        $query = null;

        switch ($validated['table_value']) {
            case 'animals':
                $query = DB::table('animals')
                    ->whereNull('deleted_at')
                    ->whereBetween('registration_date', [$validated['start_date'], $validated['end_date']]);

                // Apply pet status filter if provided
                if (!empty($validated['pet_status']) && $validated['pet_status'] !== 'all') {
                    $query->where('current_status', $validated['pet_status']);
                }

                $query->select('name', 'species', 'breed', 'age', 'gender', 'color', 'size', 'characteristics', 'registration_date', 'current_status');

                break;

            case 'adopters':
                $query = DB::table('adopters')
                    ->whereNull('deleted_at')
                    ->whereBetween('registration_date', [$validated['start_date'], $validated['end_date']]);

                $query->select('fullname', 'bday', 'city', 'state', 'zipcode', 'phone', 'residence_type', 'house_ownership', 'household_pettype', 'registration_date');

                break;

            case 'staffs':
                $query = DB::table('staffs')
                    ->whereNull('deleted_at')
                    ->whereBetween('registration_date', [$validated['start_date'], $validated['end_date']]);

                $query->select('firstname', 'lastname', 'birthday', 'email', 'city', 'state', 'zipcode', 'phone', 'registration_date');

                break;

            case 'food_inventories':
                $query = DB::table('food_inventories')->whereBetween('date_range', [$validated['start_date'], $validated['end_date']]);
                $query->select('name', 'category', 'stock_in', 'stock_out', 'date_range');

                break;

            case 'expenses':
                $query = DB::table('expenses')->whereBetween('date_range', [$validated['start_date'], $validated['end_date']]);
                $query->select('name', 'qty', 'price', 'date_range');

                break;

            default:
                return response()->json(['message' => 'Invalid table selection'], 400);
        }

        // Execute the query and get the results
        $data = $query->get();

        // Export the data using the GenericExport class
        return Excel::download(new GenericExport($data), 'generated_report_' . $validated['table_value'] . '_' . $validated['start_date'] . '_to_' . $validated['end_date'] . '.xlsx');
    }
}
