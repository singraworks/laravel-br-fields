<?php

declare(strict_types=1);

use SingraWorks\BrFields\Enums\Reason;
use SingraWorks\LaravelBrFields\Support\Message;

mutates(Message::class);

it('names the generic line of a document', function (): void {
    expect(Message::invalid('cpf'))->toBe('br-fields::validation.cpf.invalid')
        ->and(Message::invalid('cpf_or_cnpj'))->toBe('br-fields::validation.cpf_or_cnpj.invalid');
});

it('names one line per rejection reason', function (Reason $reason, string $line): void {
    expect(Message::reason('cnpj', $reason))->toBe('br-fields::validation.cnpj.'.$line);
})->with([
    [Reason::WrongLength, 'wrong_length'],
    [Reason::IllegalCharacter, 'illegal_character'],
    [Reason::RepeatedCharacters, 'repeated_characters'],
    [Reason::InvalidCheckDigit, 'invalid_check_digit'],
]);

it('has a line in every shipped locale for every key it can produce', function (string $locale): void {
    /** @var array<string, array<string, string>> $lines */
    $lines = require dirname(__DIR__, 3).'/lang/'.$locale.'/validation.php';

    foreach (['cpf', 'cnpj'] as $document) {
        expect($lines[$document])->toHaveKey('invalid');

        foreach (Reason::cases() as $reason) {
            $prefix = 'br-fields::validation.'.$document.'.';

            expect($lines[$document])->toHaveKey(mb_substr(Message::reason($document, $reason), mb_strlen($prefix)));
        }
    }

    expect($lines['cpf_or_cnpj'])->toHaveKey('invalid');
})->with(['en', 'pt_BR']);
