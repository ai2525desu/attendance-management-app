<?php

namespace Tests;

use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class AdminTestCase extends TestCase
{
    use RefreshDatabase;

    protected function loginAdmin(?Admin $admin = null): Admin
    {
        $admin ??= Admin::factory()->fixed()->create();
        $this->actingAs($admin, 'admin');
        return $admin;
    }
}
