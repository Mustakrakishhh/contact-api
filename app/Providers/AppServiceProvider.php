<?php

namespace App\Providers;

use App\Contracts\TransactionalEmailProvider;
use App\Services\Email\BrevoEmailProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public $bindings = [
        TransactionalEmailProvider::class => BrevoEmailProvider::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('contact', function (Request $request): Limit {
            return Limit::perMinute((int) config('services.contact.rate_limit'))
                ->by((string) $request->ip())
                ->response(fn () => response()->json([
                    'message' => 'Слишком много обращений. Повторите попытку через минуту.',
                ], 429));
        });
    }
}
