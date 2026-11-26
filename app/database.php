<?php
declare(strict_types=1);

function database(): PDO
{
    $localConfig = dirname(__DIR__) . '/config.local.php';
    $config = (is_file($localConfig) ? require $localConfig : []) + [
        'host' => '127.0.0.1',
        'port' => '3306',
        'name' => 'gapcorn',
        'user' => 'root',
        'password' => '',
    ];

    foreach (['host', 'port', 'name', 'user', 'password'] as $key) {
        $value = getenv('GAPCORN_DB_' . strtoupper($key));
        if ($value !== false) {
            $config[$key] = $value;
        }
    }

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $config['host'], $config['port'], $config['name']);
    $foundRows = defined('Pdo\Mysql::ATTR_FOUND_ROWS') ? Pdo\Mysql::ATTR_FOUND_ROWS : PDO::MYSQL_ATTR_FOUND_ROWS;
    $db = new PDO($dsn, $config['user'], $config['password'], [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        $foundRows => true,
    ]);
    $db->exec("SET time_zone = '+00:00'");
    return $db;
}
function currentReward(PDO $db): array
{
    $reward = $db->query('
        SELECT id, points, effective_at
        FROM reward_settings
        WHERE effective_at <= UTC_TIMESTAMP(3)
        ORDER BY effective_at DESC, id DESC LIMIT 1
    ')->fetch();
    if ($reward === false) {
        throw new RuntimeException('Import schema.sql to initialize the reward settings.');
    }
    return $reward;
}

function writeWithCurrentReward(PDO $db, callable $write): mixed
{
    $db->beginTransaction();
    try {
        // Serialize reward changes and sign-ups on the initial history row.
        $anchor = $db->query('SELECT id FROM reward_settings WHERE id = 1 FOR UPDATE');
        if ($anchor->fetchColumn() === false) {
            throw new RuntimeException('Import schema.sql to initialize the reward settings.');
        }
        $anchor->closeCursor();
        $current = currentReward($db);
        $result = $write($current);
        $db->commit();
        return $result;
    } catch (Throwable $exception) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $exception;
    }
}
