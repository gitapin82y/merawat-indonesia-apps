<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\User;
use App\Models\Admin;
use App\Models\Article;
use Carbon\Carbon;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the sitemap';

    public function handle()
    {
        $this->info('Generating sitemap...');
        
        $sitemap = Sitemap::create();
        
        // Tambahkan halaman statis
        $sitemap->add(Url::create('/')
            ->setLastModificationDate(Carbon::yesterday())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(1.0));
            
        $sitemap->add(Url::create('/eksplore-kampanye')
            ->setLastModificationDate(Carbon::yesterday())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(0.9));
            
        $sitemap->add(Url::create('/galang-dana')
            ->setLastModificationDate(Carbon::yesterday())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            ->setPriority(0.8));

        $sitemap->add(Url::create('/artikel')
            ->setLastModificationDate(Carbon::yesterday())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            ->setPriority(0.7));
            
        $sitemap->add(Url::create('/kalkulator-zakat')
            ->setLastModificationDate(Carbon::yesterday())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
            ->setPriority(0.7));
            
        $sitemap->add(Url::create('/leaderboard')
            ->setLastModificationDate(Carbon::yesterday())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(0.6));
            
        $sitemap->add(Url::create('/login')
            ->setLastModificationDate(Carbon::yesterday())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
            ->setPriority(0.5));
            
        $sitemap->add(Url::create('/register')
            ->setLastModificationDate(Carbon::yesterday())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
            ->setPriority(0.5));
            
        $sitemap->add(Url::create('/privacy-policy')
            ->setLastModificationDate(Carbon::yesterday())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
            ->setPriority(0.4));
            
        $sitemap->add(Url::create('/terms-of-service')
            ->setLastModificationDate(Carbon::yesterday())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
            ->setPriority(0.4));
            
        $sitemap->add(Url::create('/data-deletion')
            ->setLastModificationDate(Carbon::yesterday())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
            ->setPriority(0.4));
            
        // Tambahkan halaman kampanye
        Campaign::where('status', 'aktif')->get()->each(function (Campaign $campaign) use ($sitemap) {
            $sitemap->add(Url::create("/kampanye/{$campaign->slug}")
                ->setLastModificationDate($campaign->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.8));
                
            $sitemap->add(Url::create("/kampanye/{$campaign->slug}/donasi")
                ->setLastModificationDate($campaign->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.7));
        });
        
        // Tambahkan halaman kategori
        Category::all()->each(function (Category $category) use ($sitemap) {
            $sitemap->add(Url::create("/eksplore?category={$category->name}")
                ->setLastModificationDate($category->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.7));
        });

          // Tambahkan halaman artikel
        Article::all()->each(function (Article $article) use ($sitemap) {
            $sitemap->add(Url::create("/artikel/{$article->slug}")
                ->setLastModificationDate($article->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.6));
        });
        
        // Tambahkan halaman profil donatur (opsional - berbasis user)
        User::where('role', 'donatur')->get()->each(function (User $user) use ($sitemap) {
            $sitemap->add(Url::create("/donatur/{$user->name}")
                ->setLastModificationDate($user->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.5));
        });
        
        // Tambahkan halaman profil galang dana (jika model Admin digunakan untuk galang dana)
        Admin::where('status', 'disetujui')->get()->each(function (Admin $admin) use ($sitemap) {
            $sitemap->add(Url::create("/galang-dana/{$admin->name}")
                ->setLastModificationDate($admin->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.6));
        });
        
        // Simpan sitemap
        $sitemap->writeToFile(public_path('sitemap.xml'));
        
        $this->info('Sitemap generated!');
    }
}