<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $locale = App::getLocale();
        $user = $request->user();

        return [
            ...parent::share($request),
            'appName' => config('app.name', 'Turget.id'),
            'locale' => $locale,
            'locales' => ['id', 'en'],
            'translations' => trans('ui', [], $locale),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'locale' => $user->locale,
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    'is_admin' => $user->hasAnyRole(['super_admin', 'content_admin', 'content_approver']),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
            'notifications' => function () use ($user) {
                if (! $user || ! \Illuminate\Support\Facades\Schema::hasTable('in_app_notifications')) {
                    return ['unread_count' => 0, 'recent' => []];
                }

                try {
                    $unread = \App\Models\InAppNotification::where('user_id', $user->id)
                        ->where('is_read', false)
                        ->count();

                    $recent = \App\Models\InAppNotification::where('user_id', $user->id)
                        ->latest('id')
                        ->take(5)
                        ->get(['id', 'title', 'message', 'type', 'action_url', 'is_read', 'created_at']);

                    return [
                        'unread_count' => $unread,
                        'recent' => $recent,
                    ];
                } catch (\Throwable $e) {
                    return ['unread_count' => 0, 'recent' => []];
                }
            },
            'favoriteIds' => function () use ($user) {
                if (! $user || ! \Illuminate\Support\Facades\Schema::hasTable('favorites')) {
                    return ['guide' => [], 'package' => [], 'destination' => []];
                }

                try {
                    $favs = \App\Models\Favorite::where('user_id', $user->id)->get(['favoritable_type', 'favoritable_id']);
                    return [
                        'guide' => $favs->where('favoritable_type', 'guide')->pluck('favoritable_id')->values()->all(),
                        'package' => $favs->where('favoritable_type', 'package')->pluck('favoritable_id')->values()->all(),
                        'destination' => $favs->where('favoritable_type', 'destination')->pluck('favoritable_id')->values()->all(),
                    ];
                } catch (\Throwable $e) {
                    return ['guide' => [], 'package' => [], 'destination' => []];
                }
            },
        ];
    }
}
