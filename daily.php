<?php
$db = new SQLite3(__DIR__ . '/data.db');
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 9;
$offset = ($page - 1) * $limit;

// Hämta artiklar
$stmt = $db->prepare("SELECT * FROM articles WHERE category='Dagens läsning' ORDER BY date DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$results = $stmt->execute();

// Räkna antal artiklar
$total = $db->querySingle("SELECT COUNT(*) FROM articles WHERE category='Dagens läsning'");
$pages = ceil($total / $limit);
?>
<!doctype html><html lang="ps" dir="rtl"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>لنډ مطالب</title>
<link rel="stylesheet" href="styles.css">
</head><body>
<?php include 'nav.php'; ?>
<main class="container">
  <h2>لنډ مطالب  -  ارشیف</h2>
  <div class="grid">
    <?php while ($row = $results->fetchArray(SQLITE3_ASSOC)): ?>
      <div class="card">
        <?php if ($row['image']): ?>
          <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="" class="article-cover">
        <?php endif; ?>
        <h3><a href="article.php?id=<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['title']); ?></a></h3>
        <p class="badge"><?php echo htmlspecialchars($row['date']); ?></p>

        <?php 
          $excerpt = strip_tags($row['excerpt']);
          if (mb_strlen($excerpt) > 100) {
              $excerpt = mb_strimwidth($excerpt, 0, 100, "…", "UTF-8");
          }
        ?>
        <p><?php echo htmlspecialchars($excerpt); ?></p>

      </div>
    <?php endwhile; ?>
  </div>

  <div class="pagination">
    <a class="btn <?php echo ($page <= 1 ? 'disabled' : ''); ?>" href="?page=<?php echo max(1, $page-1); ?>">« مخکنی</a>
    <span>پاڼه <?php echo $page; ?> / <?php echo $pages; ?></span>
    <a class="btn <?php echo ($page >= $pages ? 'disabled' : ''); ?>" href="?page=<?php echo min($pages, $page+1); ?>">بل »</a>
  </div>
</main>

<?php include 'footer.php'; renderFooter(); ?>

</body></html>
