<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getCommission()
    {
        $commission = Commission::first();

        if (!$commission) {
            // Create default commission if it doesn't exist
            $commission = Commission::create(['amount' => 5]); // Default 5%
        }

        return response()->json([
            'success' => true,
            'data' => $commission
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Commission $commission)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Commission $commission)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateCommission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $commission = Commission::first();

        if (!$commission) {
            $commission = new Commission();
        }

        $commission->amount = $request->amount;
        $commission->save();

        return response()->json([
            'success' => true,
            'message' => 'Persentase komisi berhasil diperbarui',
            'data' => $commission
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Commission $commission)
    {
        //
    }
}
