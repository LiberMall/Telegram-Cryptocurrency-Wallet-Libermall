<?php
try {
    $db = new PDO('sqlite::memory:');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("CREATE TABLE users (id INTEGER PRIMARY KEY, username TEXT)");

    $stmt = $db->prepare("INSERT INTO users(username) VALUES (?)");
    $malicious = "test'); DROP TABLE users; --";
    $stmt->execute([$malicious]);

    $row = $db->query("SELECT username FROM users WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    if ($row['username'] === $malicious) {
        echo "Injection prevented\n";
    } else {
        echo "Injection occurred\n";
        exit(1);
    }

    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetchAll();
    if (count($tables) !== 1) {
        echo "Table dropped\n";
        exit(1);
    }
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
