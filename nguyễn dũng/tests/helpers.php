<?php

declare(strict_types=1);

function testIdentifier(string $value): string
{
    if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $value) !== 1) {
        throw new InvalidArgumentException('Tên bảng/cột SQL không hợp lệ.');
    }

    return '`' . $value . '`';
}

function testTableName(array $config, string $name): string
{
    $value = $config['tables'][$name] ?? null;

    if (!is_string($value) || $value === '') {
        throw new InvalidArgumentException('Chưa có tên bảng: ' . $name);
    }

    return $value;
}

function testColumnName(array $config, string $section, string $key): ?string
{
    $value = $config[$section][$key] ?? null;

    if (!is_string($value) || $value === '') {
        return null;
    }

    return $value;
}

function testColumnExists(PDO $pdo, string $table, string $column): bool
{
    $query = 'SELECT COUNT(*) FROM information_schema.COLUMNS '
        . 'WHERE TABLE_SCHEMA = DATABASE() '
        . 'AND TABLE_NAME = :table_name '
        . 'AND COLUMN_NAME = :column_name';

    $statement = $pdo->prepare($query);
    $statement->execute([
        ':table_name' => $table,
        ':column_name' => $column,
    ]);

    return (int) $statement->fetchColumn() > 0;
}

function testTableExists(PDO $pdo, string $table): bool
{
    $query = 'SELECT 1 FROM ' . testIdentifier($table) . ' LIMIT 0';

    try {
        $pdo->query($query);
        return true;
    } catch (PDOException $error) {
        return false;
    }
}
