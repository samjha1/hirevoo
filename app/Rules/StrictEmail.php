<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrictEmail implements ValidationRule
{
    public const MESSAGE = 'Please enter a valid email address (e.g. name@company.com).';

    /** Local part required; rejects @domain.com, email.com, etc. */
    private const PATTERN = '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/';

    public static function pattern(): string
    {
        return self::PATTERN;
    }

    public static function isValid(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        $email = trim($value);
        if ($email === '' || strlen($email) > 255) {
            return false;
        }

        // Reject unicode lookalikes and missing local part (@company.com).
        if (! preg_match(self::PATTERN, $email)) {
            return false;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::isValid($value)) {
            $fail(self::MESSAGE);
        }
    }
}
