<?php

namespace App\Http\Controllers;

use App\Models\DonationLike;
use App\Models\Donation;
use Illuminate\Http\Request;

class DonationLikeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

   public function store(Request $request, $donationId)
{
    $user = auth()->user();
    $donation = Donation::findOrFail($donationId);

    $existingLike = DonationLike::where('donation_id', $donationId)
                                 ->where('user_id', $user->id)
                                 ->first();

    if ($existingLike) {
        // Jika sudah like, lakukan unlike
        $existingLike->delete();
        $status = 'unliked';
    } else {
        // Jika belum like, lakukan like
        DonationLike::create([
            'donation_id' => $donationId,
            'user_id' => $user->id,
        ]);
        $status = 'liked';
    }

    // Kembalikan jumlah like terbaru
    $count = $donation->donationLikes()->count();

    return response()->json([
        'status' => $status,
        'count' => $count, // Mengembalikan jumlah like terbaru
    ]);
}


    /**
     * Display the specified resource.
     */
    public function show(DonationLike $donationLike)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DonationLike $donationLike)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DonationLike $donationLike)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DonationLike $donationLike)
    {
        //
    }
}
