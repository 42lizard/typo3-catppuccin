<?php

declare(strict_types=1);

// Explicit local development fixture; never loaded by the extension.
if (PHP_SAPI !== 'cli' || !getenv('DDEV_HOSTNAME')) {
    throw new RuntimeException('Run this fixture from the DDEV CLI.');
}
$pdo = new PDO('mysql:host=db;dbname=db;charset=utf8mb4', 'db', 'db', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$statement = $pdo->query("SELECT uid FROM be_users WHERE admin = 1 AND deleted = 0 AND username IN ('ddev', 'catppuccin') ORDER BY username = 'ddev' DESC LIMIT 1");
$uid = $statement->fetchColumn();
if ($uid === false) {
    throw new RuntimeException('No local ddev/catppuccin admin found. Run ddev setup first.');
}
$statement = $pdo->prepare('UPDATE be_users SET username = ?, password = ?, tstamp = ? WHERE uid = ?');
$statement->execute(['ddev', password_hash('ddev', PASSWORD_ARGON2ID), time(), $uid]);
echo "Local admin configured: ddev / ddev\n";
