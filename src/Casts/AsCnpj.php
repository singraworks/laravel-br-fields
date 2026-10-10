<?php

declare(strict_types=1);

namespace SingraWorks\LaravelBrFields\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use SingraWorks\BrFields\Documents\Cnpj;

/**
 * Stores a CNPJ as its fourteen bare characters, uppercase, and reads it back
 * as a {@see Cnpj}.
 *
 * Assigning a string validates it; an invalid one throws InvalidDocument before
 * anything reaches the database. Reading a stored value that no longer
 * validates also throws, on the first access of that attribute. See
 * {@see AsCpf} for the reasoning, which is the same.
 *
 * Use a string column of at least fourteen characters, and make sure its
 * collation keeps letters, because alphanumeric CNPJs have been issued since
 * July 2026.
 *
 * @implements CastsAttributes<Cnpj, Cnpj|string>
 */
final class AsCnpj implements CastsAttributes, SerializesCastableAttributes
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Cnpj
    {
        if ($value === null) {
            return null;
        }

        return Cnpj::from($this->stored($key, $value));
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
            $value instanceof Cnpj => $value->value(),
            is_string($value) => Cnpj::from($value)->value(),
            default => throw new InvalidArgumentException(sprintf(
                'The %s attribute must be a %s, a string, or null; %s given.',
                $key,
                Cnpj::class,
                get_debug_type($value),
            )),
        };
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function serialize(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value instanceof Cnpj ? $value->value() : null;
    }

    private function stored(string $key, mixed $value): string
    {
        if (! is_string($value)) {
            throw new InvalidArgumentException(sprintf(
                'The %s column must hold a string; %s found. Store CNPJ numbers in a string column.',
                $key,
                get_debug_type($value),
            ));
        }

        return $value;
    }
}
