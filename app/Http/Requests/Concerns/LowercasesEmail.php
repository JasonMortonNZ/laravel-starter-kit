<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

trait LowercasesEmail
{
    /**
     * Lowercase the email before validation so uniqueness checks match the
     * stored (lowercased) value on every database driver.
     */
    protected function prepareForValidation(): void
    {
        $email = $this->input('email');

        if (is_string($email)) {
            $this->merge(['email' => mb_strtolower($email)]);
        }
    }
}
