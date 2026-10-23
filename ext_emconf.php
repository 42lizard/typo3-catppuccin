<?php

declare(strict_types=1);

$EM_CONF[$_EXTKEY] = [
    'title' => 'Catppuccin Backend Themes',
    'description' => 'Latte, Frappé, Macchiato and Mocha for the TYPO3 backend.',
    'category' => 'be',
    'author' => '42lizard',
    'state' => 'beta',
    'version' => '0.1.1',
    'constraints' => [
        'depends' => [
            'typo3' => '14.3.0-14.9.99',
            'backend' => '14.3.0-14.9.99',
            'php' => '8.2.0-8.5.99',
        ],
    ],
];
