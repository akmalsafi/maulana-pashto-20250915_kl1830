<?php
$db = new SQLite3(__DIR__ . '/data.db');

// Skapa tabellen om den inte redan finns
$db->exec("CREATE TABLE IF NOT EXISTS verses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    text   TEXT,
    source TEXT
)");

// Lägg in exempelverser om tabellen är tom
$count = $db->querySingle("SELECT COUNT(*) FROM verses");
if ($count == 0) {
    $db->exec("INSERT INTO verses (text, source) VALUES
        ('او د خدای په رسی باندې ټینګ ونیسئ او نه جلا کېږئ', 'قرآن ۳:۱۰۳'),
        ('الله د زړونو سکون راکوي', 'قرآن ۱۳:۲۸'),
        ('ستاسې لپاره دین بشپړ کړم', 'قرآن ۵:۳')
    ");
    echo "✅ Verser insatta!";
} else {
    echo "✅ Tabell finns redan med $count rader.";
}
