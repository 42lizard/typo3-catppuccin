<?php

declare(strict_types=1);

// Local DDEV configuration, copied to config/system/additional.php by ddev setup.
// Honour custom DDEV TLDs without allowing arbitrary hosts in the extension.
$host = parse_url(getenv('DDEV_PRIMARY_URL') ?: 'https://catppuccin.ddev.site', PHP_URL_HOST);
$GLOBALS['TYPO3_CONF_VARS']['SYS']['trustedHostsPattern'] = '^' . preg_quote($host, '/') . '(?::[0-9]+)?$';
$GLOBALS['TYPO3_CONF_VARS']['BE']['debug'] = false;
