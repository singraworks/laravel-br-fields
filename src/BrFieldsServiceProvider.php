<?php

declare(strict_types=1);

namespace SingraWorks\LaravelBrFields;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Factory;
use Illuminate\Validation\Validator;
use SingraWorks\LaravelBrFields\Rules\Cnpj;
use SingraWorks\LaravelBrFields\Rules\Cpf;
use SingraWorks\LaravelBrFields\Rules\CpfOrCnpj;

/**
 * Wires the package into a Laravel application.
 *
 * Registers the `br-fields` translation namespace, publishes it under the
 * `br-fields-lang` tag, and teaches the validator the string rules `cpf`,
 * `cnpj` and `cpf_or_cnpj` so they can sit in a pipe-delimited rule list next
 * to `required` and `string`.
 */
final class BrFieldsServiceProvider extends ServiceProvider
{
    /**
     * Rule name to the class carrying its logic.
     *
     * @var array<string, class-string<Cnpj|Cpf|CpfOrCnpj>>
     */
    private const array STRING_RULES = [
        'cpf' => Cpf::class,
        'cnpj' => Cnpj::class,
        'cpf_or_cnpj' => CpfOrCnpj::class,
    ];

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'br-fields');

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/br-fields'),
        ], 'br-fields-lang');

        $this->callAfterResolving('validator', $this->registerStringRules(...));
    }

    /**
     * The string forms report one generic message per rule. The replacer
     * supplies it only when the application has not defined its own
     * `validation.<rule>` line, so an app override keeps winning.
     */
    private function registerStringRules(Factory $factory): void
    {
        foreach (self::STRING_RULES as $name => $rule) {
            $factory->extend(
                $name,
                static fn (string $attribute, mixed $value): bool => new $rule()->passes($value),
            );

            $factory->replacer(
                $name,
                static function (string $message, string $attribute, string $rule, array $parameters, ValidatorContract $validator) use ($name): string {
                    if ($message !== 'validation.'.$name || ! $validator instanceof Validator) {
                        return $message;
                    }

                    $translated = $validator->getTranslator()->get('br-fields::validation.'.$name.'.invalid', [
                        'attribute' => $validator->getDisplayableAttribute($attribute),
                    ]);

                    return is_string($translated) ? $translated : $message;
                },
            );
        }
    }
}
