# laravel-br-fields

[![CI](https://github.com/singraworks/laravel-br-fields/actions/workflows/ci.yml/badge.svg)](https://github.com/singraworks/laravel-br-fields/actions/workflows/ci.yml)
[![Downloads](https://img.shields.io/packagist/dt/singraworks/laravel-br-fields)](https://packagist.org/packages/singraworks/laravel-br-fields)

Validation rules and Eloquent casts for Brazilian CPF and CNPJ numbers, for Laravel applications. The checking itself lives in [`singraworks/br-fields`](https://github.com/singraworks/br-fields); this package is the Laravel surface for it: rules with translated messages, and casts that store the bare number and hand back a value object.

## Install

```bash
composer require singraworks/laravel-br-fields
```

> Requires **PHP 8.4+** and **Laravel 12+**. The service provider is discovered automatically.

## Validation

Three rules: `Cpf`, `Cnpj`, and `CpfOrCnpj` for a field that may hold either. Each one also exists as a string rule, `cpf`, `cnpj`, and `cpf_or_cnpj`.

```php
use SingraWorks\LaravelBrFields\Rules\Cnpj;
use SingraWorks\LaravelBrFields\Rules\Cpf;
use SingraWorks\LaravelBrFields\Rules\CpfOrCnpj;

$request->validate([
    'cpf' => ['required', new Cpf],
    'cnpj' => ['nullable', new Cnpj],
    'document' => ['required', new CpfOrCnpj],
    'legacy' => 'required|cpf',
]);
```

Input may carry whitespace, `.`, `-`, and `/`, exactly as the core package accepts it. Only strings pass: a CPF that arrives as a JSON number has already lost its leading zero. Like every non-implicit Laravel rule, these skip an empty value, so pair them with `required` when the field must be filled.

The class rules name the reason in their message, so a person learns whether they typed too few digits, a stray letter, or a number that does not check out. The string rules report one generic message per rule.

| Input | `new Cpf` says | `'cpf'` says |
|---|---|---|
| `111.444.777-0` | The cpf field must have 11 digits. | The cpf field must be a valid CPF. |
| `CPF: 111.444.777-35` | The cpf field may only contain digits. | The cpf field must be a valid CPF. |
| `111.444.777-00` | The cpf field must be a valid CPF. | The cpf field must be a valid CPF. |

`CpfOrCnpj` judges an eleven-character value as a CPF and a fourteen-character one as a CNPJ, and reports that document's reason. Any other length gets "must be a valid CPF or CNPJ".

### Messages

English and Brazilian Portuguese ship in the package and follow the application locale. To change a line, publish the files and edit them under `lang/vendor/br-fields`:

```bash
php artisan vendor:publish --tag=br-fields-lang
```

The usual Laravel overrides also work: an inline message passed to the validator, a `validation.custom` entry, or a `validation.cpf` line in the application's own `validation.php` for the string rule.

## Casts

`AsCpf` and `AsCnpj` store the bare number and return a `Cpf` or `Cnpj` from the core package. Assigning a string validates it first, so an invalid number throws `InvalidDocument` before anything reaches the database.

```php
use SingraWorks\LaravelBrFields\Casts\AsCnpj;
use SingraWorks\LaravelBrFields\Casts\AsCpf;

final class Customer extends Model
{
    protected function casts(): array
    {
        return [
            'cpf' => AsCpf::class,
            'cnpj' => AsCnpj::class,
        ];
    }
}

$customer->cpf = '111.444.777-35';     // stored as '11144477735'
$customer->cpf->formatted();           // '111.444.777-35'
$customer->cpf->redacted();            // '***.444.777-**'
$customer->toArray()['cpf'];           // '11144477735'
```

Two things to know before adopting a cast on an existing table:

- **Reading a stored value that does not validate throws**, on first access of that attribute. An instance is proof of validity, and the cast will not hand out a false one. Clean such rows first, or render them with the static `Cpf::format()` and `Cpf::redact()` from the core package, which check shape only.
- **Use a string column.** An integer column drops leading zeros, and nothing downstream can put them back. For CNPJ, make it at least 14 characters and keep letters intact: alphanumeric CNPJs have been issued since July 2026.

## Development

```bash
composer install
composer test           # the full gate: Rector and Pint dry runs, PHPStan, type coverage, code coverage
composer lint           # apply Rector and Pint fixes
composer analyse        # PHPStan only
composer test:mutate    # mutation testing
composer ci:check       # what CI runs
```

Thresholds are in [`composer.json`](composer.json). Coverage needs [pcov](https://github.com/krakjoe/pcov) or Xdebug. CI runs the gate on PHP 8.4 and 8.5 with both the lowest and the highest dependency sets, which covers Laravel 12 and 13.

## License

MIT. See [LICENSE](LICENSE). Maintained by [Singra](https://singraworks.com).
