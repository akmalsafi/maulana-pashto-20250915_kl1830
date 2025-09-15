<?php
session_start();

// Skydda admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$db = new SQLite3(__DIR__ . '/data.db');

// Säkerställ att frågetabellen finns
$db->exec("CREATE TABLE IF NOT EXISTS questions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    question TEXT,
    answer TEXT,
    date TEXT DEFAULT CURRENT_TIMESTAMP
)");

// ---- LÄGG TILL ----
if (isset($_POST['add'])) {
    $question = $_POST['question'] ?? '';
    $answer   = $_POST['answer'] ?? '';

    $stmt = $db->prepare("INSERT INTO questions (question, answer) VALUES (:q, :a)");
    $stmt->bindValue(':q', $question, SQLITE3_TEXT);
    $stmt->bindValue(':a', $answer, SQLITE3_TEXT);
    $stmt->execute();
}

// ---- TA BORT ----
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->exec("DELETE FROM questions WHERE id=$id");
    header("Location: admin_questions.php");
    exit;
}

// ---- UPPDATERA ----
if (isset($_POST['update'])) {
    $id       = (int)($_POST['id'] ?? 0);
    $question = $_POST['question'] ?? '';
    $answer   = $_POST['answer'] ?? '';

    $stmt = $db->prepare("UPDATE questions 
                          SET question=:q, answer=:a 
                          WHERE id=:id");
    $stmt->bindValue(':q', $question, SQLITE3_TEXT);
    $stmt->bindValue(':a', $answer, SQLITE3_TEXT);
    $stmt->bindValue(':id',$id, SQLITE3_INTEGER);
    $stmt->execute();

    header("Location: admin_questions.php");
    exit;
}
?>
<!doctype html>
<html lang="sv">
<head>
  <meta charset="utf-8">
  <title>Admin – Frågor</title>
  <link rel="stylesheet" href="styles.css">

  <!-- TinyMCE -->
  <script src="https://cdn.tiny.cloud/1/autczrpyzfk1nlrgo7n3f4g3buxhojq9wwl77qpj7h5tgoym/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
  <script>
  // Editor för fråga
  tinymce.init({
    selector: 'textarea#question',
    menubar: false,
    plugins: 'lists code',
    toolbar: 'undo redo | bold italic underline | bullist numlist | code',
    branding: false,
    min_height: 120,
    setup: ed => ed.on('change', () => ed.save())
  });

  // Editor för svar
  tinymce.init({
    selector: 'textarea#answer',
    menubar: false,
    plugins: 'link lists code',
    toolbar: 'undo redo | bold italic underline | bullist numlist | link | code',
    branding: false,
    min_height: 300,
    setup: ed => ed.on('change', () => ed.save())
  });
  </script>
</head>
<body class="container">

<h1>Admin – Frågor</h1>

<?php
// ===== REDIGERA =====
if (isset($_GET['edit'])):
    $editId = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM questions WHERE id=:id");
    $stmt->bindValue(':id',$editId,SQLITE3_INTEGER);
    $res = $stmt->execute();
    $q = $res ? $res->fetchArray(SQLITE3_ASSOC) : null;

    if ($q):
?>
  <h2>✏️ Redigera fråga</h2>
  <form method="post" style="max-width:800px;margin:auto;display:flex;flex-direction:column;gap:1rem">
    <input type="hidden" name="id" value="<?php echo (int)$q['id']; ?>">

    <label for="question">Fråga</label>
    <textarea id="question" name="question" rows="3" required><?php echo htmlspecialchars($q['question']); ?></textarea>

    <label for="answer">Svar</label>
    <textarea id="answer" name="answer" rows="10" required><?php echo htmlspecialchars($q['answer']); ?></textarea>

    <button type="submit" name="update" class="btn" style="padding:1rem;font-size:1.2rem;width:100%">Uppdatera fråga</button>
  </form>

  <p style="margin-top:1rem"><a href="admin_questions.php">⬅️ Tillbaka</a></p>

<?php
    else:
        echo "<p>❌ Fråga hittades inte.</p><p><a href='admin_questions.php'>Tillbaka</a></p>";
    endif;

// ===== LISTA + LÄGG TILL =====
else:
?>

<h2>➕ Lägg till ny fråga</h2>
<form method="post" style="max-width:800px;margin:auto;display:flex;flex-direction:column;gap:1rem">
  <label for="question">Fråga</label>
  <textarea id="question" name="question" rows="3" required></textarea>

  <label for="answer">Svar</label>
  <textarea id="answer" name="answer" rows="10" required></textarea>

  <button type="submit" name="add" class="btn" style="padding:1rem;font-size:1.2rem;width:100%">Spara fråga</button>
</form>

<hr>

<h2>📑 Befintliga frågor</h2>
<table border="1" cellpadding="5" cellspacing="0" style="width:100%;margin-top:1rem;border-collapse:collapse">
<tr><th>ID</th><th>Fråga</th><th>Datum</th><th>Åtgärder</th></tr>
<?php
$res = $db->query("SELECT * FROM questions ORDER BY date DESC");
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    echo "<tr>";
    echo "<td>".$row['id']."</td>";
    echo "<td>".htmlspecialchars($row['question'])."</td>";
    echo "<td>".htmlspecialchars($row['date'])."</td>";
    echo "<td>
            <a href='admin_questions.php?edit=".$row['id']."'>✏️ Redigera</a> | 
            <a href='admin_questions.php?delete=".$row['id']."' onclick=\"return confirm('Är du säker?')\">🗑️ Ta bort</a>
          </td>";
    echo "</tr>";
}
?>
</table>

<p><a href="logout.php">Logga ut</a></p>

<?php endif; ?>
</body>
</html>
