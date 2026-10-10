<?php

declare(strict_types=1);

namespace SingraWorks\LaravelBrFields\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use SingraWorks\BrFields\Documents\Cnpj as Document;
use SingraWorks\BrFields\Exceptions\InvalidDocument;
use SingraWorks\LaravelBrFields\Support\Message;

/**
 * Passes when the value is a string holding a valid CNPJ, numeric or
 * alphanumeric. Letters are accepted in either case.
 *
 * Only strings are accepted; see {@see Cpf} for why. The failure message names
 * the reason. The string form `'cnpj'` reports one generic message instead.
 */
final class Cnpj implements ValidationRule
{
    private const string DOCUMENT = 'cnpj';

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
