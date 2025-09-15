<?php
$db = new SQLite3(__DIR__ . '/data.db');

// Hämta alla frågor i kategorin Q&A
$stmt = $db->query("SELECT id, title, content, date FROM articles WHERE category='Q&A' ORDER BY date DESC");
?>
<!doctype html>
<html lang="ps" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Questions and Maulana's answers</title>
<link rel="stylesheet" href="styles.css">
<style>
.faq-container {
  max-width: 800px;
  margin: 2rem auto;
}
.faq-item {
  border: 1px solid var(--border);
  border-radius: 8px;
  margin-bottom: 0.75rem;
  overflow: hidden;
  box-shadow: var(--shadow);
  background: var(--card);
}
.faq-question {
  width: 100%;
  text-align: right;
  padding: 1rem;
  background: var(--card);
  font-size: 1.1rem;
  font-weight: bold;
  border: none;
  cursor: pointer;
}
.faq-answer {
  display: none;
  padding: 1rem;
  border-top: 1px solid var(--border);
  font-size: 1rem;
  line-height: 1.6;
  color: var(--ink);
}
.faq-item.active .faq-answer {
  display: block;
}
.faq-item.active .faq-question {
  background: var(--muted-bg);
}
</style>
</head>
<body>
<?php include 'nav.php'; ?>

<main class="container">
  <div class="faq-container">
    <h2>❓ سوالونه او ځوابونه</h2>
    <?php while ($row = $stmt->fetchArray(SQLITE3_ASSOC)): 
      $title   = htmlspecialchars_decode(strip_tags($row['title'] ?? ''));
      $content = htmlspecialchars_decode(strip_tags($row['content'] ?? ''));
    ?>
      <div class="faq-item">
        <button class="faq-question"><?php echo $title; ?></button>
        <div class="faq-answer">
          <p><?php echo nl2br($content); ?></p>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</main>

<script>
document.querySelectorAll(".faq-question").forEach(btn => {
  btn.addEventListener("click", () => {
    const item = btn.parentElement;
    item.classList.toggle("active");
  });
});
</script>

<?php include 'footer.php'; renderFooter(); ?>
</body>
</html>
