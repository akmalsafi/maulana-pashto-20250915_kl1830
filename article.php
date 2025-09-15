<?php
$db = new SQLite3(__DIR__ . '/data.db');

/**
 * Hämtar relaterade artiklar säkert och snabbt.
 */
function getRelatedArticles(SQLite3 $db, string $category, int $currentId, int $limit = 4): SQLite3Result {
    $countStmt = $db->prepare("
        SELECT COUNT(*) AS c
        FROM articles
        WHERE category = :category AND id <> :currentId
    ");
    $countStmt->bindValue(':category',  $category,  SQLITE3_TEXT);
    $countStmt->bindValue(':currentId', $currentId, SQLITE3_INTEGER);
    $countRes  = $countStmt->execute();
    $countRow  = $countRes ? $countRes->fetchArray(SQLITE3_ASSOC) : ['c' => 0];
    $count     = (int)($countRow['c'] ?? 0);

    $offsetMax = max(0, $count - $limit);
    $offset    = $offsetMax > 0 ? random_int(0, $offsetMax) : 0;

    $stmt = $db->prepare("
        SELECT id, title, image
        FROM articles
        WHERE category = :category AND id <> :currentId
        ORDER BY id DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':category',  $category,  SQLITE3_TEXT);
    $stmt->bindValue(':currentId', $currentId, SQLITE3_INTEGER);
    $stmt->bindValue(':limit',     $limit,     SQLITE3_INTEGER);
    $stmt->bindValue(':offset',    $offset,    SQLITE3_INTEGER);

    return $stmt->execute();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("SELECT * FROM articles WHERE id = :id");
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$res = $stmt->execute();
$article = $res->fetchArray(SQLITE3_ASSOC);

if (!$article) {
    echo "❌ Artikel hittades inte.";
    exit;
}

$apiKey   = "XfEuQS4hVf0ddoryv5uaMnzNC7WLmdyedl5UsHj2C0w";
$keywords = "nature,sea,religion,sunset,mosque,spirituality,peace,universe,stars,sad,birds";
$url      = "https://api.unsplash.com/photos/random?query=$keywords&count=10&client_id=$apiKey&sig=" . rand(1,99999);

$response = @file_get_contents($url);
if ($response !== false) {
    $data = json_decode($response, true);
    if (isset($data[0])) {
        $random      = $data[array_rand($data)];
        $unsplashImg = $random['urls']['regular'] ?? "assets/fallback.jpg";
    } else {
        $unsplashImg = $data['urls']['regular'] ?? "assets/fallback.jpg";
    }
} else {
    $unsplashImg = "assets/fallback.jpg";
}

$relatedDaily  = getRelatedArticles($db, 'Dagens läsning',  $id, 4);
$relatedWeekly = getRelatedArticles($db, 'Veckans läsning', $id, 4);
?>
<!doctype html>
<html lang="ps" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars($article['title']); ?></title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<?php include 'nav.php'; ?>

<main class="container">

  <div class="article-hero" style="background-image:url('<?php echo $unsplashImg; ?>')">
    <h1><?php echo htmlspecialchars($article['title']); ?></h1>
    <p><?php echo htmlspecialchars($article['date']); ?></p>
  </div>

  <div class="article-container">

    <?php if (!empty($article['excerpt'])): ?>
      <div style="font-size:1.3rem;color:#555;margin:1rem 0;font-weight:bold;">
        <?php echo $article['excerpt']; ?>
      </div>
    <?php endif; ?>

    <div class="article-layout">
      <?php if ($article['image']): ?>
        <div class="article-image">
          <img src="<?php echo htmlspecialchars($article['image']); ?>" alt="">
        </div>
      <?php endif; ?>

      <div class="article-text">
        <?php echo $article['content']; ?>
      </div>
    </div>
  </div>

  <p style="text-align:center;margin-top:1rem">
    <a class="btn" href="index.php">⬅️ بېرته کور ته</a>
  </p>

  <div class="related-section">
    <h2>📖 ورته مطالب</h2>
    <div class="related-grid">
      <?php while ($row = $relatedDaily->fetchArray(SQLITE3_ASSOC)): ?>
        <div class="related-card">
          <img src="<?php echo htmlspecialchars($row['image'] ?: 'assets/fallback.jpg'); ?>" alt="Related Article">
          <h4><a href="article.php?id=<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['title']); ?></a></h4>
        </div>
      <?php endwhile; ?>
    </div>
  </div>

  <div class="related-section">
    <h2>📚 ځانګړي مطالب</h2>
    <div class="related-grid">
      <?php while ($row = $relatedWeekly->fetchArray(SQLITE3_ASSOC)): ?>
        <div class="related-card">
          <img src="<?php echo htmlspecialchars($row['image'] ?: 'assets/fallback.jpg'); ?>" alt="Related Article">
          <h4><a href="article.php?id=<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['title']); ?></a></h4>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</main>

<?php include 'footer.php'; renderFooter(); ?>

</body>
</html>
