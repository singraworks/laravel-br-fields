<?php

declare(strict_types=1);

namespace SingraWorks\LaravelBrFields\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use SingraWorks\BrFields\Documents\Cnpj;
use SingraWorks\BrFields\Documents\Cpf;
use SingraWorks\LaravelBrFields\Casts\AsCnpj;
use SingraWorks\LaravelBrFields\Casts\AsCpf;

/**
 * A person or a company, for exercising both casts on one row.
 *
 * @property null|Cpf $cpf
 * @property null|Cnpj $cnpj
 */
final class Party extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    /**
     * @return array<string, class-string>
     */
    protected function casts(): array
    {
        return [
            'cpf' => AsCpf::class,
            'cnpj' => AsCnpj::class,
        ];
    }
}
