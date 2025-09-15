<?php
$db = new SQLite3(__DIR__ . '/data.db');
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// Hämta Q&A-artiklar
$stmt = $db->prepare("SELECT * FROM articles WHERE category='Q&A' ORDER BY date DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$results = $stmt->execute();

// Räkna antal Q&A-artiklar
$total = $db->querySingle("SELECT COUNT(*) FROM articles WHERE category='Q&A'");
$pages = ceil($total / $limit);
?>
<!doctype html>
<html lang="ps" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Q&A</title>
<link rel="stylesheet" href="styles.css">
<style>
/* Q&A grid */
.qa-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr); /* 4 kort per rad */
  gap: 1rem;
}
.qa-card {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 1rem;
  box-shadow: var(--shadow);
  font-size: 1.1rem;
  line-height: 1.6;
  color: var(--ink);
  text-align: center;
  min-width: 200px;  /* fast bredd */
}
</style>
</head>
<body>
<?php include 'nav.php'; ?>

<main class="container">
  <h2>❓ Q&A</h2>
 <div class="qa-grid">
  <?php while ($row = $results->fetchArray(SQLITE3_ASSOC)): ?>
    <div class="qa-card">
      <a href="question.php?id=<?php echo $row['id']; ?>" style="text-decoration:none;color:inherit;">
        <?php echo htmlspecialchars($row['title']); ?>
      </a>
    </div>
  <?php endwhile; ?>
</div>


  <div class="pagination" style="margin-top
