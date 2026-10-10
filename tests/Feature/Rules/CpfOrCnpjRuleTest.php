<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Validator;
use SingraWorks\LaravelBrFields\Rules\CpfOrCnpj;

mutates(CpfOrCnpj::class);

it('passes either document', function (string $input): void {
    expect(Validator::make(['document' => $input], ['document' => new CpfOrCnpj])->passes())->toBeTrue()
        ->and(Validator::make(['document' => $input], ['document' => 'cpf_or_cnpj'])->passes())->toBeTrue();
})->with([
    'cpf' => '111.444.777-35',
    'cnpj' => '11.222.333/0001-81',
    'alphanumeric cnpj' => '12.ABC.345/01DE-35',
]);

it('judges an eleven-character value as a cpf and a fourteen-character one as a cnpj', function (string $input, string $message): void {
    $validator = Validator::make(['document' => $input], ['document' => new CpfOrCnpj]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('document'))->toBe($message);
})->with([
    'cpf check digits' => ['111.444.777-00', 'The document field must be a valid CPF.'],
    'cpf letters' => ['111.444.777-3A', 'The document field may only contain digits.'],
    'cnpj check digits' => ['11.222.333/0001-00', 'The document field must be a valid CNPJ.'],
]);

it('names both documents when the length fits neither', function (mixed $input): void {
    $validator = Validator::make(['document' => $input], ['document' => new CpfOrCnpj]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('document'))->toBe('The document field must be a valid CPF or CNPJ.');
})->with([
    'twelve characters' => '111444777351',
    'one character' => '1',
    'integer' => 11144477735,
]);

it('fails the string form for any length', function (string $input): void {
    $validator = Validator::make(['document' => $input], ['document' => 'cpf_or_cnpj']);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('document'))->toBe('The document field must be a valid CPF or CNPJ.');
})->with([
    'cpf check digits' => '111.444.777-00',
    'cnpj check digits' => '11.222.333/0001-00',
    'twelve characters' => '111444777351',
]);

it('speaks portuguese when the application does', function (): void {
    app()->setLocale('pt_BR');

    $validator = Validator::make(['document' => '1'], ['document' => new CpfOrCnpj]);

    expect($validator->errors()->first('document'))->toBe('O campo document deve ser um CPF ou CNPJ válido.');
});
