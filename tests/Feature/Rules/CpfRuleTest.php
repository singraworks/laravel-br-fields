<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Validator;
use SingraWorks\LaravelBrFields\Rules\Cpf;

mutates(Cpf::class);

it('passes a valid cpf, punctuated or bare', function (string $input): void {
    expect(Validator::make(['cpf' => $input], ['cpf' => new Cpf])->passes())->toBeTrue()
        ->and(Validator::make(['cpf' => $input], ['cpf' => 'cpf'])->passes())->toBeTrue();
})->with([
    'masked' => '111.444.777-35',
    'bare' => '11144477735',
    'leading zero' => '012.345.678-90',
]);

it('fails with the message for the reason', function (string $input, string $message): void {
    $validator = Validator::make(['cpf' => $input], ['cpf' => new Cpf]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('cpf'))->toBe($message);
})->with([
    'wrong check digits' => ['111.444.777-00', 'The cpf field must be a valid CPF.'],
    'repeated' => ['111.111.111-11', 'The cpf field must be a valid CPF.'],
    'too short' => ['1114447773', 'The cpf field must have 11 digits.'],
    'letters' => ['CPF: 111.444.777-35', 'The cpf field may only contain digits.'],
]);

it('rejects anything that is not a string', function (mixed $input): void {
    $validator = Validator::make(['cpf' => $input], ['cpf' => new Cpf]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('cpf'))->toBe('The cpf field must be a valid CPF.');
})->with([
    'integer' => 11144477735,
    'array' => [['111.444.777-35']],
    'boolean' => true,
]);

it('speaks portuguese when the application does', function (): void {
    app()->setLocale('pt_BR');

    $validator = Validator::make(['cpf' => '1114447773'], ['cpf' => new Cpf]);

    expect($validator->errors()->first('cpf'))->toBe('O campo cpf deve ter 11 dígitos.');
});

it('uses the displayable attribute name', function (): void {
    $validator = Validator::make(['person' => ['cpf' => '1']], ['person.cpf' => new Cpf]);

    expect($validator->errors()->first('person.cpf'))->toBe('The person.cpf field must have 11 digits.');
});

it('respects nullable and required like any other rule', function (): void {
    expect(Validator::make(['cpf' => null], ['cpf' => ['nullable', new Cpf]])->passes())->toBeTrue()
        ->and(Validator::make(['cpf' => null], ['cpf' => ['required', new Cpf]])->fails())->toBeTrue()
        ->and(Validator::make([], ['cpf' => ['nullable', 'cpf']])->passes())->toBeTrue();
});

it('reports one generic message from the string form', function (): void {
    $validator = Validator::make(['cpf' => '1114447773'], ['cpf' => 'required|cpf']);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('cpf'))->toBe('The cpf field must be a valid CPF.');
});

it('translates the string form message too', function (): void {
    app()->setLocale('pt_BR');

    $validator = Validator::make(['cpf' => 'x'], ['cpf' => 'cpf']);

    expect($validator->errors()->first('cpf'))->toBe('O campo cpf deve ser um CPF válido.');
});

it('lets the application override the string form message', function (): void {
    app('translator')->addLines(['validation.cpf' => 'Bad :attribute.'], 'en');

    $validator = Validator::make(['cpf' => 'x'], ['cpf' => 'cpf']);

    expect($validator->errors()->first('cpf'))->toBe('Bad cpf.');
});

it('lets an inline message win over both forms', function (): void {
    $inline = Validator::make(['cpf' => 'x'], ['cpf' => 'cpf'], ['cpf.cpf' => 'Nope.']);
    $class = Validator::make(['cpf' => 'x'], ['cpf' => new Cpf], ['cpf.'.Cpf::class => 'Nope.']);

    expect($inline->errors()->first('cpf'))->toBe('Nope.')
        ->and($class->errors()->first('cpf'))->toBe('Nope.');
});

it('skips an empty value like every non-implicit rule, so pair it with required', function (): void {
    expect(Validator::make(['cpf' => ''], ['cpf' => new Cpf])->passes())->toBeTrue()
        ->and(Validator::make(['cpf' => ''], ['cpf' => ['required', new Cpf]])->fails())->toBeTrue();
});
