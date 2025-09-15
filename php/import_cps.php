<?php
$category = $_GET['category'] ?? 'Okänd';

// Kör python-skriptet
$output = shell_exec("python C:\xampp\htdocs\maulana-pashto-site\php\py\import_cps.py 2>&1");


echo "✅ Import klar<br><pre>$output</pre>";
