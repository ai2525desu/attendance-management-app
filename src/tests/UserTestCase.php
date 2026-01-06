<?php

namespace Tests;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class UserTestCase extends TestCase
{
    use RefreshDatabase;

    protected function loginUser(?User $user = null): User
    {
        /** @var User $user */
        $user ??= User::factory()->create();
        $this->actingAs($user, 'web');
        return $user;
    }
}
