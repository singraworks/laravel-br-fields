<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use SingraWorks\LaravelBrFields\BrFieldsServiceProvider;
use SingraWorks\LaravelBrFields\Rules\Cpf;

mutates(BrFieldsServiceProvider::class);

it('registers every string rule', function (string $rule): void {
    expect(Validator::make(['value' => 'x'], ['value' => $rule])->errors()->first('value'))
        ->toBe('The value field must be a valid '.match ($rule) {
            'cpf' => 'CPF',
            'cnpj' => 'CNPJ',
            default => 'CPF or CNPJ',
        }.'.');
})->with(['cpf', 'cnpj', 'cpf_or_cnpj']);

it('publishes the translations under the vendor directory', function (): void {
    $target = app()->langPath('vendor/br-fields');

    try {
        $this->artisan('vendor:publish', ['--tag' => 'br-fields-lang'])->assertSuccessful();

        expect(File::exists($target.'/en/validation.php'))->toBeTrue()
            ->and(File::exists($target.'/pt_BR/validation.php'))->toBeTrue();
    } finally {
        File::deleteDirectory($target);
    }
});

it('prefers a published translation over the shipped one', function (): void {
    $target = app()->langPath('vendor/br-fields/en');

    try {
        File::ensureDirectoryExists($target);
        File::put($target.'/validation.php', "<?php return ['cpf' => ['wrong_length' => 'Eleven digits, please.']];");

        $validator = Validator::make(['cpf' => '1'], ['cpf' => new Cpf]);

        expect($validator->errors()->first('cpf'))->toBe('Eleven digits, please.');
    } finally {
        File::deleteDirectory(dirname($target));
    }
});
