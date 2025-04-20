<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // The order of seeders is important due to relationships
        $this->call([
            // Core system and reference tables first
            UsersTableSeeder::class,
            CategoriesTableSeeder::class,
            ManualPaymentMethodsTableSeeder::class,
            DonationSourcesTableSeeder::class,
            AdsenseTableSeeder::class,
            CommissionTableSeeder::class,
            BannersTableSeeder::class,
            
            // Then entities with relationships
            AdminsTableSeeder::class,
            CampaignsTableSeeder::class,
            PrioritasCampaignsTableSeeder::class,
            DonationsTableSeeder::class,
            DonationLikesTableSeeder::class,
            FundraisingsTableSeeder::class,
            KabarTerbaruTableSeeder::class,
            WithdrawalsTableSeeder::class,
            UserCampaignSaveTableSeeder::class,
            NotificationsTableSeeder::class,
        ]);
    }
}