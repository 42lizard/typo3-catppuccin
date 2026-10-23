<?php

declare(strict_types=1);

define('TYPO3', true);

$root = dirname(__DIR__);
$originalItems = [
    ['label' => 'Fresh', 'value' => 'fresh'],
    ['label' => 'Modern', 'value' => 'modern'],
    ['label' => 'Classic', 'value' => 'classic'],
    ['label' => 'Another extension', 'value' => 'other'],
];
$config = ['type' => 'select', 'renderType' => 'selectSingle', 'items' => $originalItems];
$GLOBALS['TCA']['be_users']['columns']['user_settings']['columns']['theme'] = [
    'label' => 'Existing label', 'config' => $config,
];
require $root . '/Configuration/TCA/Overrides/be_users.php';
$field = $GLOBALS['TCA']['be_users']['columns']['user_settings']['columns']['theme'];
$items = $field['config']['items'];
$check = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};
$check(array_slice($items, 0, 4) === $originalItems, 'Existing theme items were changed.');
$check(count($items) === 8, 'Expected exactly four new themes.');
$check(array_column(array_slice($items, 4), 'value') === [
    'catppuccin-latte', 'catppuccin-frappe', 'catppuccin-macchiato', 'catppuccin-mocha',
], 'Unexpected theme identifiers/order.');
unset($field['config']['items']);
unset($config['items']);
$check($field === ['label' => 'Existing label', 'config' => $config], 'Existing field configuration was changed.');
$GLOBALS['TYPO3_CONF_VARS']['BE']['stylesheets'] = ['other' => 'EXT:other/backend.css'];
require $root . '/ext_localconf.php';
$styles = $GLOBALS['TYPO3_CONF_VARS']['BE']['stylesheets'];
$check($styles['other'] === 'EXT:other/backend.css', 'Another extension stylesheet was changed.');
$check($styles['catppuccin'] === 'EXT:catppuccin/Resources/Public/Css/catppuccin.css', 'Missing stylesheet registration.');
$check(is_file($root . '/Resources/Public/Css/catppuccin.css'), 'Missing distributed stylesheet.');
echo "Theme registration preserves existing settings and registers four flavours.\n";
