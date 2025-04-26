<?php
namespace App\Providers;

use App\Repositories\Eloquent\BaseRepository;
use App\Repositories\Eloquent\LeadRepository;
use App\Repositories\Interfaces\LeadRepositoryInterface;
use App\Repositories\Interfaces\RepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(RepositoryInterface::class, BaseRepository::class);
        $this->app->bind(LeadRepositoryInterface::class, LeadRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}