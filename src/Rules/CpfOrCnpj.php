<?php

declare(strict_types=1);

namespace SingraWorks\LaravelBrFields\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use SingraWorks\BrFields\Support\Normalizer;
use SingraWorks\LaravelBrFields\Support\Message;

/**
 * Passes when the value is a valid CPF or a valid CNPJ, for fields that hold
 * either a person or a company.
 *
 * Length decides which document the value is judged as: eleven characters
 * after normalisation means CPF, fourteen means CNPJ, and the failure message
 * is that document's. Any other length fails with one message naming both,
 * because neither reason would be the right one to show.
 */
final class CpfOrCnpj implements ValidationRule
{
    private const string DOCUMENT = 'cpf_or_cnpj';

    private const int CPF_LENGTH = 11;

    private const int CNPJ_LENGTH = 14;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $rule = is_string($value) ? $this->ruleFor($value) : null;

        if (! $rule instanceof ValidationRule) {
            $fail(Message::invalid(self::DOCUMENT))->translate();

            return;
        }

        $rule->validate($attribute, $value, $fail);
    }

    /** The same verdict, without a message. Backs the string rule. */
    public function passes(mixed $value): bool
    {
        return is_string($value) && $this->ruleFor($value)?->passes($value) === true;
    }

    private function ruleFor(string $value): Cnpj|Cpf|null
    {
        return match (mb_strlen(Normalizer::strip($value))) {
            self::CPF_LENGTH => new Cpf(),
            self::CNPJ_LENGTH => new Cnpj(),
            default => null,
        };
    }
}
