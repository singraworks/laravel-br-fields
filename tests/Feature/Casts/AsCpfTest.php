<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SingraWorks\BrFields\Documents\Cpf;
use SingraWorks\BrFields\Enums\Reason;
use SingraWorks\BrFields\Exceptions\InvalidDocument;
use SingraWorks\LaravelBrFields\Casts\AsCpf;
use SingraWorks\LaravelBrFields\Tests\Fixtures\Party;

mutates(AsCpf::class);

beforeEach(function (): void {
    Schema::create('parties', function (Blueprint $table): void {
        $table->id();
        $table->string('cpf', 11)->nullable();
        $table->string('cnpj', 14)->nullable();
    });
});

it('stores the bare digits and reads back a cpf', function (): void {
    $party = Party::create(['cpf' => '111.444.777-35']);

    expect($party->getAttributes()['cpf'])->toBe('11144477735');

    $fresh = Party::query()->findOrFail($party->getKey());

    expect($fresh->cpf)->toBeInstanceOf(Cpf::class)
        ->and($fresh->cpf?->formatted())->toBe('111.444.777-35');
});

it('accepts a cpf instance', function (): void {
    $party = new Party;
    $party->cpf = Cpf::from('012.345.678-90');

    expect($party->getAttributes()['cpf'])->toBe('01234567890')
        ->and($party->cpf?->value())->toBe('01234567890');
});

it('keeps null', function (): void {
    $party = Party::create(['cpf' => null]);

    expect($party->cpf)->toBeNull()
        ->and(Party::query()->findOrFail($party->getKey())->cpf)->toBeNull()
        ->and($party->toArray())->toHaveKey('cpf', null);
});

it('refuses an invalid value before it reaches the database', function (): void {
    $party = new Party;

    try {
        $party->cpf = '111.444.777-00';
        $this->fail('Expected InvalidDocument');
    } catch (InvalidDocument $invalidDocument) {
        expect($invalidDocument->reason)->toBe(Reason::InvalidCheckDigit)
            ->and($party->getAttributes())->not->toHaveKey('cpf');
    }
});

it('refuses a value that is neither a cpf, a string, nor null', function (): void {
    $party = new Party;

    expect(fn (): int => $party->cpf = 11144477735)
        ->toThrow(InvalidArgumentException::class, 'The cpf attribute must be a SingraWorks\BrFields\Documents\Cpf, a string, or null; int given.');
});

it('refuses to hand out an instance for a stored value that does not validate', function (): void {
    Party::query()->insert(['cpf' => '11144477700']);

    $party = Party::query()->firstOrFail();

    expect(fn (): ?Cpf => $party->cpf)->toThrow(InvalidDocument::class);
});

it('refuses a stored value that is not a string', function (): void {
    $party = new Party;
    $party->setRawAttributes(['cpf' => 11144477735]);

    expect(fn (): ?Cpf => $party->cpf)
        ->toThrow(InvalidArgumentException::class, 'The cpf column must hold a string; int found. Store CPF numbers in a string column.');
});

it('serialises to the bare digits', function (): void {
    $party = Party::create(['cpf' => '111.444.777-35']);

    expect($party->toArray()['cpf'])->toBe('11144477735')
        ->and($party->toJson())->toBe('{"cpf":"11144477735","id":1}');
});

it('serialises null without touching the cast', function (): void {
    $cast = new AsCpf;

    expect($cast->serialize(new Party, 'cpf', null, []))->toBeNull();
});

it('is not dirty when the same cpf is assigned in another spelling', function (): void {
    $party = Party::create(['cpf' => '111.444.777-35']);
    $party->cpf = '11144477735';

    expect($party->isDirty('cpf'))->toBeFalse();

    $party->cpf = '012.345.678-90';

    expect($party->isDirty('cpf'))->toBeTrue();
});
