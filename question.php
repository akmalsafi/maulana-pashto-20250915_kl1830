<?php
$db = new SQLite3(__DIR__ . '/data.db');
$id = (int)($_GET['id'] ?? 0);

// Hämta vald fråga
$stmt = $db->prepare("SELECT * FROM articles WHERE id=:id AND category='Q&A'");
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$res = $stmt->execute();
$question = $res->fetchArray(SQLITE3_ASSOC);

if (!$question) {
    echo "❌ Frågan hittades inte.";
    exit;
}

// Hämta nästa fråga (id > nuvarande)
$nextStmt = $db->prepare("SELECT id, title FROM articles WHERE category='Q&A' AND id > :id ORDER BY id ASC LIMIT 1");
$nextStmt->bindValue(':id', $id, SQLITE3_INTEGER);
$nextRes = $nextStmt->execute();
$nextQuestion = $nextRes->fetchArray(SQLITE3_ASSOC);

// Om ingen nästa fråga hittades → hoppa till första
if (!$nextQuestion) {
    $firstStmt = $db->query("SELECT id, title FROM articles WHERE category='Q&A' ORDER BY id ASC LIMIT 1");
    $nextQuestion = $firstStmt->fetchArray(SQLITE3_ASSOC);
}

// Hämta föregående fråga (id < nuvarande)
$prevStmt = $db->prepare("SELECT id, title FROM articles WHERE category='Q&A' AND id < :id ORDER BY id DESC LIMIT 1");
$prevStmt->bindValue(':id', $id, SQLITE3_INTEGER);
$prevRes = $prevStmt->execute();
$prevQuestion = $prevRes->fetchArray(SQLITE3_ASSOC);

// Om ingen föregående fråga hittades → hoppa till sista
if (!$prevQuestion) {
    $lastStmt = $db->query("SELECT id, title FROM articles WHERE category='Q&A' ORDER BY id DESC LIMIT 1");
    $prevQuestion = $lastStmt->fetchArray(SQLITE3_ASSOC);
}

// Hämta fler frågor (15 st)
$related = $db->query("SELECT id, title FROM articles WHERE category='Q&A' AND id != $id ORDER BY date DESC LIMIT 15");
?>
<!doctype html>
<html lang="ps" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars($question['title']); ?></title>
<link rel="stylesheet" href="styles.css">
<style>
.question-container {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 2rem;
  box-shadow: var(--shadow);
}
.question-title {
  font-size: 1.4rem;
  font-weight: bold;
  margin-bottom: 1rem;
}
.question-answer {
  font-size: 1.1rem;
  line-height: 1.8;
  color: var(--ink);
}
.next-prev {
  margin-top: 2rem;
}
.next-prev a {
  display: block;
  margin: .5rem 0;
  text-align: center;
  padding: .75rem;
  border: 1px solid var(--border);
  border-radius: 8px;
  background: var(--card);
  text-decoration: none;
}
.next-prev span {
  display: block;
  font-weight: bold;
  margin-bottom: .25rem;
}
.related-qa {
  margin-top: 2rem;
}
.related-qa h3 {
  margin-bottom: 1rem;
}
.related-qa ul {
  list-style: none;
  padding: 0;
}
.related-qa li {
  margin-bottom: .5rem;
}
.related-qa a {
  text-decoration: none;
  color: var(--accent);
}
</style>
</head>
<body>
<?php include 'nav.php'; ?>

<main class="container">
  <div class="question-container">
    <div class="question-title">
      <?php echo htmlspecialchars($question['title']); ?>
    </div>
    <div class="question-answer">
      <?php echo nl2br($question['content']); ?>
    </div>

    <div class="next-prev">
      <?php if ($prevQuestion): ?>
        <a class="btn" href="question.php?id=<?php echo $prevQuestion['id']; ?>">
          <span>👈</span>
         <b> <?php echo htmlspecialchars($prevQuestion['title']); ?>
        </a>
      <?php endif; ?>

      <?php if ($nextQuestion): ?>
        <a class="btn" href="question.php?id=<?php echo $nextQuestion['id']; ?>">
          <span>👉</span>
          <?php echo htmlspecialchars($nextQuestion['title']); ?>
        </a>
      <?php endif; ?>
    </div>
  </div>

  <div class="related-qa">
    <h3>📌 Andra frågor</h3>
    <ul>
      <?php while ($row = $related->fetchArray(SQLITE3_ASSOC)): ?>
        <li><a href="question.php?id=<?php echo $row['id']; ?>">
          <?php echo htmlspecialchars($row['title']); ?>
        </a></li>
      <?php endwhile; ?>
    </ul>
  </div>
</main>

<?php include 'footer.php'; renderFooter(); ?>
</body>
</html>
