<?php
$db = new SQLite3(__DIR__ . '/data.db');

// Ta bort den gamla tabellen om den är fel
$db->exec("DROP TABLE IF EXISTS verses");

// Skapa rätt tabell med kolumnerna text och source
$db->exec("CREATE TABLE verses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    text   TEXT NOT NULL,
    source TEXT NOT NULL
)");

// Lägg in några exempelverser
$db->exec("INSERT INTO verses (text, source) VALUES
    ('او د خدای په رسی باندې ټینګ ونیسئ او نه جلا کېږئ', 'قرآن ۳:۱۰۳'),
    ('الله د زړونو سکون راکوي', 'قرآن ۱۳:۲۸'),
    ('ستاسې لپاره دین بشپړ کړم', 'قرآن ۵:۳')
");

echo "✅ Tabell `verses` fixad och fylld med exempel.";
