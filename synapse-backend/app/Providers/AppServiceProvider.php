<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use App\Models\PersonalAccessToken;
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->app->booted(function () {
            Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        });

        config([
    'filesystems.disks.cloudinary' => [
        'driver' => 'cloudinary',
        'url' => 'cloudinary://' . env('CLOUDINARY_KEY') . ':' . env('CLOUDINARY_SECRET') . '@' . env('CLOUDINARY_CLOUD_NAME'),
    ],
]);
    }
}