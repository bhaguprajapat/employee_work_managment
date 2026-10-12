<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Creates the first administrator. The password is read interactively (or from
 * the APP_ADMIN_PASSWORD environment variable) so no credentials are stored in code.
 *
 *   php artisan app:create-admin
 */
class CreateAdminUser extends Command
{
    protected $signature = 'app:create-admin
                            {--name= : Administrator full name}
                            {--email= : Administrator email address}';

    protected $description = 'Create the initial administrator account securely';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Full name');
        $email = $this->option('email') ?: $this->ask('Email address');

        $password = env('APP_ADMIN_PASSWORD') ?: $this->secret('Password (min 8 chars, mixed case, number)');

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:120'],
                'email' => ['required', 'email:rfc', 'max:190', 'unique:users,email'],
                'password' => ['required', Password::min(8)->mixedCase()->numbers()],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        // The User model casts 'password' to hashed, so pass the plain value here.
        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        $this->info("Administrator {$email} created. You can now sign in.");

        return self::SUCCESS;
    }
}
