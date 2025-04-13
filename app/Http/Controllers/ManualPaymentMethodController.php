<?php

namespace App\Http\Controllers;

use App\Models\ManualPaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ManualPaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $methods = ManualPaymentMethod::all();
        return response()->json($methods);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'instructions' => 'nullable|string',
            'is_active' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        
        if ($request->hasFile('icon')) {
            $path = $request->file('icon')->store('payment_icons', 'public');
            $data['icon'] = $path;
        }

        $method = ManualPaymentMethod::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Metode pembayaran berhasil ditambahkan',
            'data' => $method
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(ManualPaymentMethod $paymentMethod)
    {
        return response()->json($paymentMethod);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $paymentMethod = ManualPaymentMethod::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'instructions' => 'nullable|string',
            'is_active' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        
        if ($request->hasFile('icon')) {
            // Hapus icon lama jika ada
            if ($paymentMethod->icon) {
                Storage::disk('public')->delete($paymentMethod->icon);
            }
            
            $path = $request->file('icon')->store('payment_icons', 'public');
            $data['icon'] = $path;
        }

        $paymentMethod->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Metode pembayaran berhasil diperbarui',
            'data' => $paymentMethod
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $paymentMethod = ManualPaymentMethod::findOrFail($id);
        
        // Hapus icon jika ada
        if ($paymentMethod->icon) {
            Storage::disk('public')->delete($paymentMethod->icon);
        }
        
        $paymentMethod->delete();

        return response()->json([
            'success' => true,
            'message' => 'Metode pembayaran berhasil dihapus'
        ]);
    }

    /**
     * Get active payment methods.
     */
    public function getActive()
    {
        $methods = ManualPaymentMethod::where('is_active', true)->get();
        return response()->json($methods);
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus($id)
    {
        $paymentMethod = ManualPaymentMethod::findOrFail($id);
        $paymentMethod->is_active = !$paymentMethod->is_active;
        $paymentMethod->save();

        return response()->json([
            'success' => true,
            'message' => 'Status metode pembayaran berhasil diubah',
            'data' => $paymentMethod
        ]);
    }
}