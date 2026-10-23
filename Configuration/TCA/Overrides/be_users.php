<?php

declare(strict_types=1);

defined('TYPO3') || exit;

// Extend the native selector; preserve Core and other extensions' items/config.
foreach ([
    'latte' => 'Latte',
    'frappe' => 'Frappé',
    'macchiato' => 'Macchiato',
    'mocha' => 'Mocha',
] as $flavour => $label) {
    $GLOBALS['TCA']['be_users']['columns']['user_settings']['columns']['theme']['config']['items'][] = [
        'label' => 'Catppuccin ' . $label,
        'value' => 'catppuccin-' . $flavour,
    ];
}
