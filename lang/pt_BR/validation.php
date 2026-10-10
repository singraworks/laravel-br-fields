<?php

declare(strict_types=1);

return [
    'cpf' => [
        'invalid' => 'O campo :attribute deve ser um CPF válido.',
        'wrong_length' => 'O campo :attribute deve ter 11 dígitos.',
        'illegal_character' => 'O campo :attribute deve conter apenas números.',
        'repeated_characters' => 'O campo :attribute deve ser um CPF válido.',
        'invalid_check_digit' => 'O campo :attribute deve ser um CPF válido.',
    ],
    'cnpj' => [
        'invalid' => 'O campo :attribute deve ser um CNPJ válido.',
        'wrong_length' => 'O campo :attribute deve ter 14 caracteres.',
        'illegal_character' => 'O campo :attribute deve conter apenas números e letras.',
        'repeated_characters' => 'O campo :attribute deve ser um CNPJ válido.',
        'invalid_check_digit' => 'O campo :attribute deve ser um CNPJ válido.',
    ],
    'cpf_or_cnpj' => [
        'invalid' => 'O campo :attribute deve ser um CPF ou CNPJ válido.',
    ],
];
