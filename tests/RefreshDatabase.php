<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabaseState;

trait RefreshDatabase
{
    public function refreshDatabase()
    {
        if (! RefreshDatabaseState::$migrated) {
            $this->artisan('migrate:fresh', [
                '--database' => 'mysql',
                '--path' => 'database/migrations',
                '--seed' => true,
            ]);

            RefreshDatabaseState::$migrated = true;
        }
    }
}
