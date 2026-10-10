<?php

declare(strict_types=1);

return [
    'cpf' => [
        'invalid' => 'The :attribute field must be a valid CPF.',
        'wrong_length' => 'The :attribute field must have 11 digits.',
        'illegal_character' => 'The :attribute field may only contain digits.',
        'repeated_characters' => 'The :attribute field must be a valid CPF.',
        'invalid_check_digit' => 'The :attribute field must be a valid CPF.',
    ],
    'cnpj' => [
        'invalid' => 'The :attribute field must be a valid CNPJ.',
        'wrong_length' => 'The :attribute field must have 14 characters.',
        'illegal_character' => 'The :attribute field may only contain digits and letters.',
        'repeated_characters' => 'The :attribute field must be a valid CNPJ.',
        'invalid_check_digit' => 'The :attribute field must be a valid CNPJ.',
    ],
    'cpf_or_cnpj' => [
        'invalid' => 'The :attribute field must be a valid CPF or CNPJ.',
    ],
];
