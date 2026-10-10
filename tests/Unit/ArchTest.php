<?php

declare(strict_types=1);

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Validation\ValidationRule;

arch()->preset()->php();
arch()->preset()->security();

arch('rules implement the validation contract')
    ->expect('SingraWorks\LaravelBrFields\Rules')
    ->toImplement(ValidationRule::class)
    ->toBeFinal();

arch('casts implement the cast contract')
    ->expect('SingraWorks\LaravelBrFields\Casts')
    ->toImplement(CastsAttributes::class)
    ->toBeFinal();
