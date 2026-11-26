<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
if ($argc !== 2 || !is_file($argv[1])) {
    fwrite(STDERR, "Usage: php migrate-sqlite.php <path-to-sqlite-file>\n");
    exit(1);
}
require __DIR__ . '/app/database.php';

function mysqlUtc(?string $timestamp): ?string
{
    return $timestamp === null ? null : (new DateTimeImmutable($timestamp, new DateTimeZone('UTC')))
        ->setTimezone(new DateTimeZone('UTC'))
        ->format('Y-m-d H:i:s.v');
}

try {
    $source = new PDO('sqlite:' . realpath($argv[1]), options: [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $source->beginTransaction();
    $rewards = $source->query('SELECT id, points, effective_at FROM reward_settings ORDER BY id')->fetchAll();
    $customers = $source->query('SELECT * FROM customers ORDER BY id')->fetchAll();
    $hasDemo = $source->query("SELECT 1 FROM sqlite_master WHERE type = 'table' AND name = 'demo_customers'")->fetchColumn();
    $demos = $hasDemo ? $source->query('SELECT id, name, email, points, joined_at FROM demo_customers ORDER BY id')->fetchAll() : [];
    $source->commit();
    if (($rewards[0]['id'] ?? null) !== 1) {
        throw new RuntimeException('The SQLite database is missing its initial reward setting.');
    }

    $db = database();
    writeWithCurrentReward($db, static function (array $current) use ($db, $rewards, $customers, $demos): void {
        $demoPoints = $db->query('SELECT id, points FROM demo_customers ORDER BY id FOR UPDATE')->fetchAll(PDO::FETCH_KEY_PAIR);
        if ((int) $current['id'] !== 1 || (int) $current['points'] !== 10
            || (int) $db->query('SELECT COUNT(*) FROM reward_settings')->fetchColumn() !== 1
            || (int) $db->query('SELECT COUNT(*) FROM customers')->fetchColumn() !== 0
            || array_map('intval', $demoPoints) !== [1 => 120, 2 => 85, 3 => 60, 4 => 35]) {
            throw new RuntimeException('Import into a fresh MySQL database created with schema.sql to protect existing data.');
        }

        $initial = $db->prepare('UPDATE reward_settings SET points = ?, effective_at = ? WHERE id = 1');
        $initial->execute([$rewards[0]['points'], mysqlUtc($rewards[0]['effective_at'])]);
        $rewardInsert = $db->prepare('INSERT INTO reward_settings (id, points, effective_at) VALUES (?, ?, ?)');
        foreach (array_slice($rewards, 1) as $reward) {
            $rewardInsert->execute([$reward['id'], $reward['points'], mysqlUtc($reward['effective_at'])]);
        }

        $customerInsert = $db->prepare('
            INSERT INTO customers (id, email, password_hash, points, reward_id, rewarded_at, last_active_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        foreach ($customers as $customer) {
            $customerInsert->execute([
                $customer['id'], $customer['email'], $customer['password_hash'], $customer['points'], $customer['reward_id'],
                mysqlUtc($customer['rewarded_at']), mysqlUtc($customer['last_active_at'] ?? null),
            ]);
        }

        $demoUpdate = $db->prepare('UPDATE demo_customers SET name = ?, email = ?, points = ?, joined_at = ? WHERE id = ?');
        foreach ($demos as $demo) {
            $demoUpdate->execute([$demo['name'], $demo['email'], $demo['points'], mysqlUtc($demo['joined_at']), $demo['id']]);
            if ($demoUpdate->rowCount() !== 1) {
                throw new RuntimeException('The SQLite database contains an unknown demo profile.');
            }
        }
    });

    printf("Imported %d customer accounts, %d reward settings and %d demo profiles.\n", count($customers), count($rewards), count($demos));
} catch (Throwable $exception) {
    fwrite(STDERR, 'Migration failed. ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
