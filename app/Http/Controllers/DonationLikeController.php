<?php

namespace App\Http\Controllers;

use App\Models\DonationLike;
use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
    $donation = Donation::findOrFail($donationId);

    $isLoggedIn = auth()->check();

    if (!$isLoggedIn) {
        // Check if guest_identifier cookie exists
        $guestIdentifier = $request->cookie('guest_identifier');
        
        // If cookie doesn't exist, create a new one
        if (!$guestIdentifier) {
            $guestIdentifier = Str::uuid()->toString();
            // Cookie will be set in the response
        }
    }

    $existingLike = null;
        
        if ($isLoggedIn) {
            $userId = auth()->user()->id;
            $existingLike = DonationLike::where('donation_id', $donationId)
                                        ->where('user_id', $userId)
                                        ->first();
        } else {
            $existingLike = DonationLike::where('donation_id', $donationId)
                                        ->where('guest_identifier', $guestIdentifier)
                                        ->first();
        }


    if ($existingLike) {
        // If already liked, unlike
        $existingLike->delete();
        $status = 'unliked';

    } else {
           // If not liked, like
           $likeData = [
            'donation_id' => $donationId,
        ];
        
        // Set the appropriate identifier based on login status
        if ($isLoggedIn) {
            $likeData['user_id'] = auth()->user()->id;
        } else {
            $likeData['guest_identifier'] = $guestIdentifier;
        }
        
        DonationLike::create($likeData);
        $status = 'liked';
    }

    // Kembalikan jumlah like terbaru
    $count = $donation->donationLikes()->count();

    $response = response()->json([
        'status' => $status,
        'count' => $count,
    ]);
    
    // Set cookie for guest users
    if (!$isLoggedIn) {
        $response->cookie('guest_identifier', $guestIdentifier, 60 * 24 * 30); // 30 days
    }
    
    return $response;
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
