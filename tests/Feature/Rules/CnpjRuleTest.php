<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Validator;
use SingraWorks\LaravelBrFields\Rules\Cnpj;

mutates(Cnpj::class);

it('passes a valid cnpj, numeric or alphanumeric', function (string $input): void {
    expect(Validator::make(['cnpj' => $input], ['cnpj' => new Cnpj])->passes())->toBeTrue()
        ->and(Validator::make(['cnpj' => $input], ['cnpj' => 'cnpj'])->passes())->toBeTrue();
})->with([
    'numeric masked' => '11.222.333/0001-81',
    'numeric bare' => '11222333000181',
    'alphanumeric' => '12.ABC.345/01DE-35',
    'alphanumeric lowercase' => '12.abc.345/01de-35',
]);

it('fails with the message for the reason', function (string $input, string $message): void {
    $validator = Validator::make(['cnpj' => $input], ['cnpj' => new Cnpj]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('cnpj'))->toBe($message);
})->with([
    'wrong check digits' => ['11.222.333/0001-00', 'The cnpj field must be a valid CNPJ.'],
    'repeated' => ['00.000.000/0000-00', 'The cnpj field must be a valid CNPJ.'],
    'too short' => ['1122233300018', 'The cnpj field must have 14 characters.'],
    'symbols' => ['11.222.333/0001_81', 'The cnpj field may only contain digits and letters.'],
]);

it('rejects anything that is not a string', function (): void {
    $validator = Validator::make(['cnpj' => 11222333000181], ['cnpj' => new Cnpj]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('cnpj'))->toBe('The cnpj field must be a valid CNPJ.');
});

it('speaks portuguese when the application does', function (): void {
    app()->setLocale('pt_BR');

    $validator = Validator::make(['cnpj' => '11.222.333/0001-00'], ['cnpj' => new Cnpj]);

    expect($validator->errors()->first('cnpj'))->toBe('O campo cnpj deve ser um CNPJ válido.');
});

it('reports one generic message from the string form', function (): void {
    $validator = Validator::make(['cnpj' => '1122233300018'], ['cnpj' => 'required|cnpj']);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('cnpj'))->toBe('The cnpj field must be a valid CNPJ.');
});
