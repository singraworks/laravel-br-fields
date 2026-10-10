<?php

declare(strict_types=1);

namespace SingraWorks\LaravelBrFields\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use SingraWorks\BrFields\Documents\Cpf;

/**
 * Stores a CPF as its eleven bare digits and reads it back as a {@see Cpf}.
 *
 * Assigning a string validates it; an invalid one throws InvalidDocument before
 * anything reaches the database. Reading a stored value that no longer
 * validates also throws, on the first access of that attribute: an instance is
 * proof of validity, and the cast will not hand out a false one. Clean such
 * rows before adopting the cast, or render them with the static Cpf::format().
 *
 * Use a string column. An integer column drops leading zeros, and nothing
 * downstream can put them back.
 *
 * @implements CastsAttributes<Cpf, Cpf|string>
 */
final class AsCpf implements CastsAttributes, SerializesCastableAttributes
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Cpf
    {
        if ($value === null) {
            return null;
        }

        return Cpf::from($this->stored($key, $value));
    }

    /**
     * Eloquent passes whatever was assigned, so the TSet in the class-level
     * docblock is a promise to static analysis, not a runtime guarantee. The
     * guard below is what makes it true.
     *
     * @param array<string, mixed> $attributes
     *
     * @phpstan-param mixed $value
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return match (true) {
            $value === null => null,
            $value instanceof Cpf => $value->value(),
            is_string($value) => Cpf::from($value)->value(),
            default => throw new InvalidArgumentException(sprintf(
                'The %s attribute must be a %s, a string, or null; %s given.',
                $key,
                Cpf::class,
                get_debug_type($value),
            )),
        };
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function serialize(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value instanceof Cpf ? $value->value() : null;
    }

    private function stored(string $key, mixed $value): string
    {
        if (! is_string($value)) {
            throw new InvalidArgumentException(sprintf(
                'The %s column must hold a string; %s found. Store CPF numbers in a string column.',
                $key,
                get_debug_type($value),
            ));
        }

        return $value;
    }
}
