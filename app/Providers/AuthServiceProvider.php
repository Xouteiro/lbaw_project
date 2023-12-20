<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Event;
use App\Models\Invite;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Policies\EventPolicy;
use App\Policies\CommentPolicy;
use App\Policies\InvitePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Event::class => EventPolicy::class,
        Comment::class => CommentPolicy::class,
        Invite::class => InvitePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
