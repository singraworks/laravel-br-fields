<?php

declare(strict_types=1);

namespace SingraWorks\LaravelBrFields\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use SingraWorks\BrFields\Documents\Cpf as Document;
use SingraWorks\BrFields\Exceptions\InvalidDocument;
use SingraWorks\LaravelBrFields\Support\Message;

/**
 * Passes when the value is a string holding a valid CPF.
 *
 * Only strings are accepted. A CPF that arrives as a JSON number has already
 * lost any leading zero, so validating it would prove nothing about the value
 * the person typed.
 *
 * The failure message names the reason: wrong length, a character outside
 * 0-9, a repeated sequence, or check digits that do not match. The string form
 * `'cpf'` reports one generic message instead.
 */
final class Cpf implements ValidationRule
{
    private const string DOCUMENT = 'cpf';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail(Message::invalid(self::DOCUMENT))->translate();

            return;
        }

        try {
            Document::from($value);
        } catch (InvalidDocument $invalidDocument) {
            $fail(Message::reason(self::DOCUMENT, $invalidDocument->reason))->translate();
        }
    }

    /** The same verdict, without a message. Backs the string rule. */
    public function passes(mixed $value): bool
    {
        return is_string($value) && Document::isValid($value);
    }
}
