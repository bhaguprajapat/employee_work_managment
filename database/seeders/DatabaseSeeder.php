<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Intentionally creates no accounts. Accounts contain credentials, so the first
 * administrator is created with: php artisan app:create-admin
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        //
    }
}
