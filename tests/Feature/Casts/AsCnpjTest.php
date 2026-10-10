<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SingraWorks\BrFields\Documents\Cnpj;
use SingraWorks\BrFields\Enums\Reason;
use SingraWorks\BrFields\Exceptions\InvalidDocument;
use SingraWorks\LaravelBrFields\Casts\AsCnpj;
use SingraWorks\LaravelBrFields\Tests\Fixtures\Party;

mutates(AsCnpj::class);

beforeEach(function (): void {
    Schema::create('parties', function (Blueprint $table): void {
        $table->id();
        $table->string('cpf', 11)->nullable();
        $table->string('cnpj', 14)->nullable();
    });
});

it('stores the bare characters, uppercase, and reads back a cnpj', function (): void {
    $party = Party::create(['cnpj' => '12.abc.345/01de-35']);

    expect($party->getAttributes()['cnpj'])->toBe('12ABC34501DE35');

    $fresh = Party::query()->findOrFail($party->getKey());

    expect($fresh->cnpj)->toBeInstanceOf(Cnpj::class)
        ->and($fresh->cnpj?->formatted())->toBe('12.ABC.345/01DE-35')
        ->and($fresh->cnpj?->isAlphanumeric())->toBeTrue();
});

it('accepts a cnpj instance', function (): void {
    $party = new Party;
    $party->cnpj = Cnpj::from('11.222.333/0001-81');

    expect($party->getAttributes()['cnpj'])->toBe('11222333000181')
        ->and($party->cnpj?->isHeadquarters())->toBeTrue();
});

it('keeps null', function (): void {
    $party = Party::create(['cnpj' => null]);

    expect($party->cnpj)->toBeNull()
        ->and(Party::query()->findOrFail($party->getKey())->cnpj)->toBeNull();
});

it('refuses an invalid value before it reaches the database', function (): void {
    $party = new Party;

    try {
        $party->cnpj = '11.222.333/0001-00';
        $this->fail('Expected InvalidDocument');
    } catch (InvalidDocument $invalidDocument) {
        expect($invalidDocument->reason)->toBe(Reason::InvalidCheckDigit)
            ->and($party->getAttributes())->not->toHaveKey('cnpj');
    }
});

it('refuses a value that is neither a cnpj, a string, nor null', function (): void {
    $party = new Party;

    expect(fn (): int => $party->cnpj = 11222333000181)
        ->toThrow(InvalidArgumentException::class, 'The cnpj attribute must be a SingraWorks\BrFields\Documents\Cnpj, a string, or null; int given.');
});

it('refuses to hand out an instance for a stored value that does not validate', function (): void {
    Party::query()->insert(['cnpj' => '11222333000100']);

    $party = Party::query()->firstOrFail();

    expect(fn (): ?Cnpj => $party->cnpj)->toThrow(InvalidDocument::class);
});

it('refuses a stored value that is not a string', function (): void {
    $party = new Party;
    $party->setRawAttributes(['cnpj' => 11222333000181]);

    expect(fn (): ?Cnpj => $party->cnpj)
        ->toThrow(InvalidArgumentException::class, 'The cnpj column must hold a string; int found. Store CNPJ numbers in a string column.');
});

it('serialises to the bare characters', function (): void {
    $party = Party::create(['cnpj' => '11.222.333/0001-81']);

    expect($party->toArray()['cnpj'])->toBe('11222333000181')
        ->and($party->toJson())->toBe('{"cnpj":"11222333000181","id":1}');
});

it('serialises null without touching the cast', function (): void {
    $cast = new AsCnpj;

    expect($cast->serialize(new Party, 'cnpj', null, []))->toBeNull();
});
