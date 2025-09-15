<?php
$db = new SQLite3(__DIR__ . '/database.db');
$db->exec("CREATE TABLE IF NOT EXISTS verses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    arabic TEXT NOT NULL,
    pashto TEXT NOT NULL,
    reference TEXT
)");

$db->exec("INSERT INTO verses (arabic, pashto, reference) VALUES
('اللَّهُ لَا إِلَٰهَ إِلَّا هُوَ الْحَيُّ الْقَيُّومُ', 'الله (ج) شته معبود نشته مګر هغه، تل ژوندی او د هر څه ساتونکی دی', 'البقرة 255'),
('إِنَّ اللَّهَ مَعَ الصَّابِرِينَ', 'بېشکه الله د صبر کوونکو سره دی', 'البقرة 153'),
('فَاذْكُرُونِي أَذْكُرْكُمْ', 'ما یاد کړئ زه به مو یاد کړم', 'البقرة 152')
");
echo "✅ Verser tillagda!";
?>