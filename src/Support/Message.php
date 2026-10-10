<?php

declare(strict_types=1);

namespace SingraWorks\LaravelBrFields\Support;

use SingraWorks\BrFields\Enums\Reason;

/**
 * Translation keys for validation failures, in the `br-fields` namespace.
 *
 * Every line lives under `validation.<document>.<reason>`, plus an `invalid`
 * line per document for failures that carry no reason, such as a value that is
 * not a string. See lang/en/validation.php for the full list.
 *
 * @internal
 */
final class Message
{
    private const string NAMESPACE = 'br-fields::validation';

    /** The generic line for a document, e.g. `br-fields::validation.cpf.invalid`. */
    public static function invalid(string $document): string
    {
        return self::key($document, 'invalid');
    }

    /** The line for one rejection reason, e.g. `br-fields::validation.cpf.wrong_length`. */
    public static function reason(string $document, Reason $reason): string
    {
        return self::key($document, match ($reason) {
            Reason::WrongLength => 'wrong_length',
            Reason::IllegalCharacter => 'illegal_character',
            Reason::RepeatedCharacters => 'repeated_characters',
            Reason::InvalidCheckDigit => 'invalid_check_digit',
        });
    }

    private static function key(string $document, string $line): string
    {
        return sprintf('%s.%s.%s', self::NAMESPACE, $document, $line);
    }
}
