<?php

namespace App\Rules;

use App\Support\RoleEmailGuard;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EmailAvailableForRole implements ValidationRule
{
    public function __construct(private readonly string $role) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($message = RoleEmailGuard::registrationError($this->role, (string) $value)) {
            $fail($message);
        }
    }
}
