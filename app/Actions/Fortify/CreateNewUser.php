<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'national_id' => ['required', 'string', 'size:10', Rule::unique('users', 'national_id')],
            'phone' => ['nullable', 'string', 'max:20'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'language' => ['nullable', 'string', 'max:5'],
            'date_format' => ['nullable', 'string', 'max:20'],
            'time_format' => ['nullable', 'string', 'max:20'],
            'week_starts_on' => ['nullable', 'integer', 'between:0,6'],
            'avatar' => ['nullable', 'string'],
            'theme' => ['nullable', Rule::in(['light', 'dark', 'auto'])],
            'password' => $this->passwordRules(),
        ])->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'national_id' => $input['national_id'],
            'password' => $input['password'],
            'phone' => $input['phone'] ?? null,
            'job_title' => $input['job_title'] ?? null,
            'bio' => $input['bio'] ?? null,
            'timezone' => $input['timezone'] ?? 'Asia/Riyadh',
            'language' => $input['language'] ?? 'ar',
            'date_format' => $input['date_format'] ?? 'Y-m-d',
            'time_format' => $input['time_format'] ?? 'H:i',
            'week_starts_on' => $input['week_starts_on'] ?? 6,
            'avatar' => $input['avatar'] ?? null,
            'theme' => $input['theme'] ?? 'auto',
        ]);
    }
}
