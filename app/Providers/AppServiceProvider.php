<?php

namespace App\Providers;

use App\Repositories\CampaignRecipientRepository;
use App\Repositories\CampaignRepository;
use App\Repositories\Contracts\CampaignRecipientRepositoryInterface;
use App\Repositories\Contracts\CampaignRepositoryInterface;
use App\Repositories\Contracts\RecipientRepositoryInterface;
use App\Repositories\RecipientRepository;
use App\Services\CampaignRecipientService;
use App\Services\CampaignService;
use App\Services\Contracts\CampaignRecipientServiceInterface;
use App\Services\Contracts\CampaignServiceInterface;
use App\Services\Contracts\RecipientServiceInterface;
use App\Services\RecipientService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RecipientRepositoryInterface::class, RecipientRepository::class);
        $this->app->bind(RecipientServiceInterface::class, RecipientService::class);

        $this->app->bind(CampaignRepositoryInterface::class, CampaignRepository::class);
        $this->app->bind(CampaignServiceInterface::class, CampaignService::class);

        $this->app->bind(CampaignRecipientRepositoryInterface::class, CampaignRecipientRepository::class);
        $this->app->bind(CampaignRecipientServiceInterface::class, CampaignRecipientService::class);
    }

    public function boot(): void
    {
        RateLimiter::for('send-message', function () {
            // 2 jobs per 5 seconds
            return Limit::perSecond(2, 5);
        });
    }
}
