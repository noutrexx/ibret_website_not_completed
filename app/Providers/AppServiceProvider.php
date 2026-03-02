<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema; // Bunu ekle
use Illuminate\Pagination\Paginator;
use App\Models\Category;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191); // Bunu ekle
        Paginator::useBootstrapFive();

        view()->composer('frontend.layouts.app', function ($view) {
            $navCategories = cache()->remember('nav:categories', now()->addMinutes(10), function () {
                return Category::whereNull('parent_id')
                    ->with(['children:id,parent_id,name,slug,order'])
                    ->orderBy('order', 'asc')
                    ->orderBy('name', 'asc')
                    ->get(['id', 'name', 'slug', 'order']);
            });

            $sportsSlug = (string) setting('sports_category_slug', 'spor');
            $sportsNavCategory = cache()->remember('nav:sports:' . $sportsSlug, now()->addMinutes(10), function () use ($sportsSlug) {
                return Category::where('slug', $sportsSlug)
                    ->whereNull('parent_id')
                    ->with(['children:id,parent_id,name,slug,order'])
                    ->first(['id', 'name', 'slug', 'parent_id']);
            });

            $economySlug = (string) setting('economy_category_slug', 'ekonomi');
            $economyNavCategory = cache()->remember('nav:economy:' . $economySlug, now()->addMinutes(10), function () use ($economySlug) {
                return Category::where('slug', $economySlug)
                    ->whereNull('parent_id')
                    ->with(['children:id,parent_id,name,slug,order'])
                    ->first(['id', 'name', 'slug', 'parent_id']);
            });

            $view->with('navCategories', $navCategories);
            $view->with('sportsNavCategory', $sportsNavCategory);
            $view->with('sportsNavSlug', $sportsSlug);
            $view->with('economyNavCategory', $economyNavCategory);
            $view->with('economyNavSlug', $economySlug);
        });
    }
}
