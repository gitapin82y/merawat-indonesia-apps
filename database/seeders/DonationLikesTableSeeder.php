<?php

namespace Database\Seeders;

use App\Models\Donation;
use App\Models\DonationLike;
use App\Models\User;
use Illuminate\Database\Seeder;

class DonationLikesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $donations = Donation::where('status', 'sukses')->get();
        $users = User::where('role', 'donatur')->get();
        
        foreach ($donations as $donation) {
            // Some donations get no likes
            if (rand(0, 2) === 0) {
                continue;
            }
            
            // Generate between 1-5 likes per donation
            $likesCount = rand(1, 5);
            
            for ($i = 0; $i < $likesCount; $i++) {
                // Decide if like comes from registered user or guest
                $isUser = rand(0, 1) && $users->isNotEmpty();
                
                if ($isUser) {
                    // Make sure we don't create duplicate likes from same user
                    $user = $users->random();
                    
                    // Check if this user already liked this donation
                    $exists = DonationLike::where('donation_id', $donation->id)
                                        ->where('user_id', $user->id)
                                        ->exists();
                    
                    if (!$exists) {
                        DonationLike::create([
                            'donation_id' => $donation->id,
                            'user_id' => $user->id,
                            'guest_identifier' => null
                        ]);
                    }
                } else {
                    // Guest likes
                    $guestId = 'guest_' . uniqid();
                    
                    DonationLike::create([
                        'donation_id' => $donation->id,
                        'user_id' => null,
                        'guest_identifier' => $guestId
                    ]);
                }
            }
        }
    }
}