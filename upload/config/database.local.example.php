<?php

declare(strict_types=1);

/**
 * Example only. Copy to database.local.php for manual hosting setup.
 * Never commit database.local.php or real credentials to Git.
 * GitHub Actions production deploy generates database.local.php from Secrets.
 */
return [
    'host' => 'YOUR_INFINITYFREE_MYSQL_HOST',
    'port' => 3306,
    'name' => 'YOUR_FULL_DATABASE_NAME',
    'user' => 'YOUR_DATABASE_USERNAME',
    'pass' => 'YOUR_HOSTING_ACCOUNT_PASSWORD',
    'charset' => 'utf8mb4',
];
