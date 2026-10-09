<?php

// Manually parse .env (parse_ini_file fails on Laravel .env format)
$env = [];
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (!str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        // Remove surrounding quotes
        if (preg_match('/^"(.*)"$/', $value, $m)) $value = $m[1];
        elseif (preg_match("/^'(.*)'$/", $value, $m)) $value = $m[1];
        $env[$key] = $value;
    }
}

$host   = $env['DB_HOST'] ?? '127.0.0.1';
$port   = $env['DB_PORT'] ?? '3306';
$dbname = $env['DB_DATABASE'] ?? 'library_db';
$user   = $env['DB_USERNAME'] ?? 'root';
$pass   = $env['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    fwrite(STDERR, "DB connection failed: " . $e->getMessage() . "\n");
    exit(1);
}

// Framework tables to skip
$skip = [
    'cache', 'cache_locks', 'failed_jobs', 'jobs', 'job_batches',
    'migrations', 'password_resets', 'password_reset_tokens',
    'personal_access_tokens', 'sessions'
];

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
sort($tables);

echo "@startuml\n";
echo "skinparam linetype ortho\n";
echo "skinparam nodesep 50\n";
echo "skinparam ranksep 50\n";
echo "skinparam shadowing false\n";
echo "skinparam defaultFontName Segoe UI\n";
echo "skinparam defaultFontSize 11\n\n";

// Entities
foreach ($tables as $table) {
    if (in_array($table, $skip)) continue;

    echo "entity $table {\n";
    $cols = $pdo->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        $null = strtoupper($c['Null']) === 'YES' ? ' nullable' : '';
        $key  = $c['Key'] === 'PRI' ? ' <<PK>>' : ($c['Key'] === 'MUL' ? ' <<FK>>' : '');
        echo "  * {$c['Field']} : {$c['Type']}$null$key\n";
    }
    echo "}\n\n";
}

// Relationships
foreach ($tables as $table) {
    if (in_array($table, $skip)) continue;

    $stmt = $pdo->prepare("
        SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = :db
          AND TABLE_NAME = :tbl
          AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    $stmt->execute(['db' => $dbname, 'tbl' => $table]);
    $fks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($fks as $fk) {
        echo "{$fk['REFERENCED_TABLE_NAME']} ||--o{ $table : \"{$fk['COLUMN_NAME']}\"\n";
    }
}

echo "\n@enduml\n";